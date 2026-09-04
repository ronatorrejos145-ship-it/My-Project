<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerComplaint;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class ComplaintService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService
    ) {}

    public function createComplaint(
        Customer $customer,
        string $description,
        string $category = 'SERVICE_QUALITY',
        string $severity = 'HIGH',
        ?Ticket $ticket = null,
        ?int $assignedOfficerId = null
    ): CustomerComplaint {
        return DB::transaction(function () use ($customer, $description, $category, $severity, $ticket, $assignedOfficerId) {
            $cmpNum = $this->numberSequenceService->getNextNumber('COMPLAINT');

            $complaint = CustomerComplaint::create([
                'complaint_number' => $cmpNum,
                'customer_id' => $customer->id,
                'ticket_id' => $ticket?->id,
                'category' => $category,
                'severity' => $severity,
                'description' => $description,
                'status' => $assignedOfficerId ? 'INVESTIGATING' : 'RECEIVED',
                'assigned_officer_id' => $assignedOfficerId,
            ]);

            AuditLogService::log('CREATE_COMPLAINT', 'support', $complaint, null, $complaint->toArray());

            return $complaint;
        });
    }

    public function resolveComplaint(
        CustomerComplaint $complaint,
        string $rootCauseAnalysis,
        string $actionTaken,
        ?int $officerId = null
    ): CustomerComplaint {
        return DB::transaction(function () use ($complaint, $rootCauseAnalysis, $actionTaken, $officerId) {
            $complaint->update([
                'status' => 'RESOLVED',
                'root_cause_analysis' => $rootCauseAnalysis,
                'action_taken' => $actionTaken,
                'resolved_at' => now(),
                'assigned_officer_id' => $officerId ?? $complaint->assigned_officer_id,
            ]);

            AuditLogService::log('RESOLVE_COMPLAINT', 'support', $complaint, null, $complaint->toArray());

            return $complaint;
        });
    }
}
