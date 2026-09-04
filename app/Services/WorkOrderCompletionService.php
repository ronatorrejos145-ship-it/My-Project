<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\WorkOrderCustomerConfirmation;
use App\Models\WorkOrderDowntime;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Support\Facades\DB;

class WorkOrderCompletionService
{
    public function __construct(
        protected WorkOrderStateService $stateService
    ) {}

    public function completeWorkOrder(
        WorkOrder $workOrder,
        array $completionData,
        ?int $userId = null
    ): WorkOrder {
        return DB::transaction(function () use ($workOrder, $completionData, $userId) {
            // Record root cause and corrective actions
            if (!empty($completionData['actual_root_cause'])) {
                $workOrder->actual_root_cause = $completionData['actual_root_cause'];
            }
            if (!empty($completionData['corrective_action'])) {
                $workOrder->corrective_action = $completionData['corrective_action'];
            }
            if (!empty($completionData['preventive_action'])) {
                $workOrder->preventive_action = $completionData['preventive_action'];
            }

            // Customer confirmation / digital signature
            if (!empty($completionData['confirmed_by_name']) || !empty($completionData['signature_file_path'])) {
                WorkOrderCustomerConfirmation::create([
                    'work_order_id' => $workOrder->id,
                    'customer_id' => $workOrder->customer_id,
                    'confirmed_by_name' => $completionData['confirmed_by_name'] ?? 'Customer',
                    'signature_file_path' => $completionData['signature_file_path'] ?? null,
                    'rating' => $completionData['rating'] ?? 5,
                    'customer_comments' => $completionData['customer_comments'] ?? null,
                    'ip_address' => request()->ip(),
                ]);
            }

            // Record downtime & service restoration
            if (!empty($completionData['outage_start_at'])) {
                $outageStart = \Carbon\Carbon::parse($completionData['outage_start_at']);
                $outageEnd = !empty($completionData['outage_end_at']) ? \Carbon\Carbon::parse($completionData['outage_end_at']) : now();

                WorkOrderDowntime::create([
                    'work_order_id' => $workOrder->id,
                    'customer_id' => $workOrder->customer_id,
                    'subscription_id' => $workOrder->subscription_id,
                    'outage_start_at' => $outageStart,
                    'outage_end_at' => $outageEnd,
                    'duration_minutes' => $outageStart->diffInMinutes($outageEnd),
                    'is_service_restored' => true,
                    'restoration_verified_by_user_id' => $userId,
                    'notes' => 'Service restored upon work order completion',
                ]);
            }

            // Transition work order status to COMPLETED then CLOSED
            $this->stateService->transition($workOrder, 'COMPLETED', $userId, 'Completion', 'Work completed on site');
            $this->stateService->transition($workOrder, 'CLOSED', $userId, 'Closure', 'Work order verified and closed');

            // Update associated Ticket if present
            if ($workOrder->ticket_id && $workOrder->ticket) {
                $ticket = $workOrder->ticket;
                $ticket->status = 'RESOLVED';
                $ticket->resolved_at = now();
                $ticket->save();

                TicketMessage::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $userId,
                    'author_type' => 'AGENT',
                    'visibility' => 'CUSTOMER_VISIBLE',
                    'message' => 'Work Order #' . $workOrder->work_order_number . ' has been completed. Issue resolved.',
                ]);
            }

            return $workOrder;
        });
    }
}
