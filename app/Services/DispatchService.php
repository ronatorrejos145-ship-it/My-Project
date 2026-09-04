<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\WorkOrderAssignment;
use App\Models\User;
use App\Models\TechnicianSkill;
use App\Models\TechnicianCertification;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DispatchService
{
    public function __construct(
        protected WorkOrderStateService $stateService
    ) {}

    public function assignTechnician(WorkOrder $workOrder, int $technicianId, ?int $assignedByUserId = null, ?string $teamName = null, ?string $notes = null): WorkOrderAssignment
    {
        $technician = User::findOrFail($technicianId);

        // Verify certification status
        $expiredCerts = TechnicianCertification::where('technician_id', $technicianId)
            ->where('verification_status', 'VERIFIED')
            ->where('expires_at', '<', now())
            ->count();

        if ($expiredCerts > 0) {
            // Note certification expiration warning in log/notes
            $notes = ($notes ? $notes . ' | ' : '') . "Warning: Technician has {$expiredCerts} expired certification(s).";
        }

        return DB::transaction(function () use ($workOrder, $technician, $technicianId, $assignedByUserId, $teamName, $notes) {
            // Unassign previous primary if exists
            WorkOrderAssignment::where('work_order_id', $workOrder->id)
                ->where('is_primary', true)
                ->update(['status' => 'REASSIGNED']);

            $assignment = WorkOrderAssignment::create([
                'work_order_id' => $workOrder->id,
                'technician_id' => $technicianId,
                'assigned_by_user_id' => $assignedByUserId,
                'team_name' => $teamName,
                'is_primary' => true,
                'status' => 'ASSIGNED',
                'notes' => $notes,
                'assigned_at' => now(),
            ]);

            $workOrder->assigned_technician_id = $technicianId;
            $workOrder->save();

            // Transition state to ASSIGNED if currently PENDING or APPROVED
            if (in_array($workOrder->status, ['PENDING', 'APPROVED', 'DRAFT'])) {
                $this->stateService->transition($workOrder, 'ASSIGNED', $assignedByUserId, 'Dispatch Assignment', 'Assigned to ' . $technician->name);
            }

            return $assignment;
        });
    }

    public function scheduleWorkOrder(WorkOrder $workOrder, string $scheduledStartAt, string $scheduledEndAt, ?int $scheduledByUserId = null): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $scheduledStartAt, $scheduledEndAt, $scheduledByUserId) {
            $workOrder->scheduled_start_at = $scheduledStartAt;
            $workOrder->scheduled_end_at = $scheduledEndAt;
            $workOrder->save();

            if (in_array($workOrder->status, ['PENDING', 'APPROVED', 'ASSIGNED'])) {
                $this->stateService->transition($workOrder, 'SCHEDULED', $scheduledByUserId, 'Scheduling', 'Scheduled for ' . $scheduledStartAt);
            }

            return $workOrder;
        });
    }
}
