<?php

namespace App\Services;

use App\Models\InstallationSchedule;
use App\Models\InstallationStatusHistory;
use App\Models\InstallationWorkOrder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InstallationSchedulingService
{
    public function scheduleInstallation(
        InstallationWorkOrder $workOrder,
        string $scheduledDate,
        string $startTime,
        string $endTime,
        ?int $technicianId = null,
        bool $allowOverride = false,
        ?string $overrideReason = null,
        ?int $userId = null
    ): InstallationSchedule {
        return DB::transaction(function () use ($workOrder, $scheduledDate, $startTime, $endTime, $technicianId, $allowOverride, $overrideReason, $userId) {
            /** @var InstallationWorkOrder $workOrder */
            $workOrder = InstallationWorkOrder::where('id', $workOrder->id)->lockForUpdate()->firstOrFail();

            $technicianId = $technicianId ?? $workOrder->assigned_technician_id;

            if ($technicianId) {
                // Check schedule conflicts
                $conflict = InstallationSchedule::where('technician_id', $technicianId)
                    ->where('scheduled_date', $scheduledDate)
                    ->where('installation_id', '!=', $workOrder->id)
                    ->where(function ($query) use ($startTime, $endTime) {
                        $query->whereBetween('start_time', [$startTime, $endTime])
                            ->orWhereBetween('end_time', [$startTime, $endTime])
                            ->orWhere(function ($q) use ($startTime, $endTime) {
                                $q->where('start_time', '<=', $startTime)
                                  ->where('end_time', '>=', $endTime);
                            });
                    })
                    ->exists();

                if ($conflict && !$allowOverride) {
                    throw new InvalidArgumentException("Schedule conflict detected for technician ID {$technicianId} on {$scheduledDate} between {$startTime} and {$endTime}.");
                }
            }

            $schedule = InstallationSchedule::create([
                'installation_id' => $workOrder->id,
                'scheduled_date' => $scheduledDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'technician_id' => $technicianId,
                'team' => $workOrder->assigned_team,
                'appointment_status' => 'CONFIRMED',
                'is_override' => $conflict ?? false,
                'override_reason' => $overrideReason,
                'created_by' => $userId,
            ]);

            $scheduledStart = "{$scheduledDate} {$startTime}:00";
            $scheduledEnd = "{$scheduledDate} {$endTime}:00";

            $oldStatus = $workOrder->status;
            $newStatus = in_array($workOrder->status, ['PENDING', 'ASSIGNED']) ? 'SCHEDULED' : $workOrder->status;

            $workOrder->update([
                'scheduled_start' => $scheduledStart,
                'scheduled_end' => $scheduledEnd,
                'customer_appointment_status' => 'CONFIRMED',
                'status' => $newStatus,
                'updated_by' => $userId,
            ]);

            if ($oldStatus !== $newStatus) {
                InstallationStatusHistory::create([
                    'installation_id' => $workOrder->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'changed_by' => $userId,
                    'reason' => 'Installation appointment scheduled',
                ]);
            }

            AuditLogService::log(
                'SCHEDULE_INSTALLATION',
                'installations',
                $workOrder,
                ['status' => $oldStatus],
                ['scheduled_start' => $scheduledStart, 'status' => $newStatus]
            );

            return $schedule;
        });
    }
}
