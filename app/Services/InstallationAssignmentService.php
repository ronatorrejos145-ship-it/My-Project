<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\InstallationAssignment;
use App\Models\InstallationStatusHistory;
use App\Models\InstallationWorkOrder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InstallationAssignmentService
{
    public function __construct(
        protected TechnicianEligibilityService $eligibilityService
    ) {}

    public function assignTechnician(InstallationWorkOrder $workOrder, ?int $technicianId, ?string $team = null, ?int $supervisorId = null, ?string $reason = null, ?int $userId = null): InstallationWorkOrder
    {
        return DB::transaction(function () use ($workOrder, $technicianId, $team, $supervisorId, $reason, $userId) {
            /** @var InstallationWorkOrder $workOrder */
            $workOrder = InstallationWorkOrder::where('id', $workOrder->id)->lockForUpdate()->firstOrFail();

            $prevTechId = $workOrder->assigned_technician_id;
            $prevTeam = $workOrder->assigned_team;

            if ($technicianId) {
                $technician = Employee::findOrFail($technicianId);
                $eligibility = $this->eligibilityService->checkEligibility($technician, $workOrder);
                if (!$eligibility['eligible']) {
                    throw new InvalidArgumentException("Technician ineligible: " . implode(' ', $eligibility['reasons']));
                }
            }

            InstallationAssignment::create([
                'installation_id' => $workOrder->id,
                'previous_technician_id' => $prevTechId,
                'new_technician_id' => $technicianId,
                'previous_team' => $prevTeam,
                'new_team' => $team,
                'assigned_by' => $userId,
                'assignment_reason' => $reason ?? 'Technician dispatch assignment',
            ]);

            $oldStatus = $workOrder->status;
            $newStatus = ($workOrder->status === 'PENDING') ? 'ASSIGNED' : $workOrder->status;

            $workOrder->update([
                'assigned_technician_id' => $technicianId,
                'assigned_team' => $team,
                'supervisor_id' => $supervisorId ?? $workOrder->supervisor_id,
                'status' => $newStatus,
                'updated_by' => $userId,
            ]);

            if ($oldStatus !== $newStatus) {
                InstallationStatusHistory::create([
                    'installation_id' => $workOrder->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'changed_by' => $userId,
                    'reason' => 'Technician assigned to work order',
                ]);
            }

            AuditLogService::log(
                'ASSIGN_INSTALLATION',
                'installations',
                $workOrder,
                ['technician_id' => $prevTechId, 'status' => $oldStatus],
                ['technician_id' => $technicianId, 'status' => $newStatus]
            );

            return $workOrder;
        });
    }
}
