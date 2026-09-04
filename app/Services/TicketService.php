<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\ServiceAccount;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketStatusHistory;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TicketService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService,
        protected SlaManagementService $slaService
    ) {}

    public function createTicket(
        Customer $customer,
        string $subject,
        string $description,
        string $category = 'TECHNICAL',
        string $ticketType = 'INCIDENT',
        string $priority = 'NORMAL',
        string $source = 'CUSTOMER_PORTAL',
        ?ServiceAccount $serviceAccount = null,
        ?int $assignedDeptId = null,
        ?int $assignedUserId = null
    ): Ticket {
        return DB::transaction(function () use ($customer, $subject, $description, $category, $ticketType, $priority, $source, $serviceAccount, $assignedDeptId, $assignedUserId) {
            $tktNum = $this->numberSequenceService->getNextNumber('TICKET');

            $slaPolicy = SlaPolicy::where('priority', $priority)->where('is_active', true)->first();
            $slaDeadlines = $this->slaService->calculateSlaDeadlines($priority, $slaPolicy);

            $status = $assignedUserId ? 'ASSIGNED' : 'NEW';

            $ticket = Ticket::create([
                'ticket_number' => $tktNum,
                'customer_id' => $customer->id,
                'service_account_id' => $serviceAccount?->id,
                'subject' => $subject,
                'description' => $description,
                'category' => $category,
                'ticket_type' => $ticketType,
                'priority' => $priority,
                'status' => $status,
                'source' => $source,
                'assigned_department_id' => $assignedDeptId,
                'assigned_user_id' => $assignedUserId,
                'sla_policy_id' => $slaPolicy?->id,
                'first_response_due_at' => $slaDeadlines['first_response_due'],
                'resolution_due_at' => $slaDeadlines['resolution_due'],
            ]);

            // Initial customer message entry
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $customer->user_id,
                'author_type' => 'CUSTOMER',
                'visibility' => 'CUSTOMER_VISIBLE',
                'message' => $description,
            ]);

            TicketStatusHistory::create([
                'ticket_id' => $ticket->id,
                'previous_status' => null,
                'new_status' => $status,
                'reason' => 'Initial ticket creation',
            ]);

            AuditLogService::log('CREATE_TICKET', 'support', $ticket, null, $ticket->toArray());

            return $ticket;
        });
    }

    public function addMessage(
        Ticket $ticket,
        string $message,
        string $visibility = 'CUSTOMER_VISIBLE', // CUSTOMER_VISIBLE, INTERNAL_ONLY
        ?int $userId = null,
        string $authorType = 'AGENT'
    ): TicketMessage {
        return DB::transaction(function () use ($ticket, $message, $visibility, $userId, $authorType) {
            $msg = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $userId,
                'author_type' => $authorType,
                'visibility' => $visibility,
                'message' => $message,
            ]);

            if ($authorType === 'AGENT' && !$ticket->first_responded_at) {
                $ticket->update(['first_responded_at' => now()]);
            }

            if ($authorType === 'CUSTOMER' && $ticket->status === 'WAITING_CUSTOMER') {
                $this->updateStatus($ticket, 'IN_PROGRESS', 'Customer replied', $userId);
            }

            return $msg;
        });
    }

    public function updateStatus(Ticket $ticket, string $newStatus, ?string $reason = null, ?int $changedBy = null): Ticket
    {
        return DB::transaction(function () use ($ticket, $newStatus, $reason, $changedBy) {
            $oldStatus = $ticket->status;
            if ($oldStatus === $newStatus) {
                return $ticket;
            }

            $updateData = ['status' => $newStatus];

            if ($newStatus === 'RESOLVED' && !$ticket->resolved_at) {
                $updateData['resolved_at'] = now();
            }

            if ($newStatus === 'CLOSED' && !$ticket->closed_at) {
                $updateData['closed_at'] = now();
            }

            $ticket->update($updateData);

            TicketStatusHistory::create([
                'ticket_id' => $ticket->id,
                'previous_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $reason,
                'changed_by' => $changedBy,
            ]);

            AuditLogService::log('UPDATE_TICKET_STATUS', 'support', $ticket, ['status' => $oldStatus], ['status' => $newStatus]);

            return $ticket;
        });
    }
}
