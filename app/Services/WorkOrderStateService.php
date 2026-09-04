<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\WorkOrderStatusHistory;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WorkOrderStateService
{
    /**
     * Allowed state transitions map
     */
    protected array $allowedTransitions = [
        'DRAFT' => ['PENDING', 'CANCELLED'],
        'PENDING' => ['APPROVED', 'ASSIGNED', 'SCHEDULED', 'CANCELLED'],
        'APPROVED' => ['ASSIGNED', 'SCHEDULED', 'CANCELLED'],
        'ASSIGNED' => ['SCHEDULED', 'EN_ROUTE', 'IN_PROGRESS', 'REASSIGNED', 'CANCELLED'],
        'SCHEDULED' => ['EN_ROUTE', 'ON_SITE', 'IN_PROGRESS', 'RESCHEDULED', 'CANCELLED'],
        'EN_ROUTE' => ['ON_SITE', 'IN_PROGRESS', 'FAILED', 'CANCELLED'],
        'ON_SITE' => ['IN_PROGRESS', 'WAITING_MATERIALS', 'WAITING_CUSTOMER', 'WAITING_EXTERNAL', 'FAILED', 'CANCELLED'],
        'IN_PROGRESS' => ['WAITING_MATERIALS', 'WAITING_CUSTOMER', 'WAITING_EXTERNAL', 'TESTING', 'COMPLETED', 'FAILED', 'RESCHEDULED', 'CANCELLED'],
        'WAITING_MATERIALS' => ['IN_PROGRESS', 'CANCELLED'],
        'WAITING_CUSTOMER' => ['IN_PROGRESS', 'CANCELLED'],
        'WAITING_EXTERNAL' => ['IN_PROGRESS', 'CANCELLED'],
        'TESTING' => ['COMPLETED', 'IN_PROGRESS', 'FAILED'],
        'COMPLETED' => ['CLOSED'],
        'FAILED' => ['RESCHEDULED', 'CLOSED', 'CANCELLED'],
        'RESCHEDULED' => ['PENDING', 'ASSIGNED', 'SCHEDULED', 'CANCELLED'],
        'CANCELLED' => [],
        'CLOSED' => [],
    ];

    public function transition(WorkOrder $workOrder, string $newStatus, ?int $userId = null, ?string $reason = null, ?string $notes = null, ?string $ip = null): WorkOrder
    {
        $currentStatus = strtoupper($workOrder->status);
        $targetStatus = strtoupper($newStatus);

        if ($currentStatus === $targetStatus) {
            return $workOrder;
        }

        $allowed = $this->allowedTransitions[$currentStatus] ?? [];
        if (!in_array($targetStatus, $allowed, true)) {
            throw new InvalidArgumentException("Invalid work order status transition from {$currentStatus} to {$targetStatus}.");
        }

        return DB::transaction(function () use ($workOrder, $currentStatus, $targetStatus, $userId, $reason, $notes, $ip) {
            $workOrder->status = $targetStatus;

            if ($targetStatus === 'ON_SITE' && !$workOrder->arrival_at) {
                $workOrder->arrival_at = now();
            }
            if ($targetStatus === 'IN_PROGRESS' && !$workOrder->actual_start_at) {
                $workOrder->actual_start_at = now();
            }
            if (in_array($targetStatus, ['COMPLETED', 'CLOSED'], true) && !$workOrder->completion_at) {
                $workOrder->completion_at = now();
                $workOrder->actual_end_at = now();
            }

            $workOrder->save();

            WorkOrderStatusHistory::create([
                'work_order_id' => $workOrder->id,
                'old_status' => $currentStatus,
                'new_status' => $targetStatus,
                'changed_by_user_id' => $userId,
                'reason' => $reason,
                'notes' => $notes,
                'ip_address' => $ip,
            ]);

            return $workOrder;
        });
    }
}
