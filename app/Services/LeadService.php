<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LeadService
{
    protected NumberSequenceService $sequenceService;

    public function __construct(NumberSequenceService $sequenceService)
    {
        $this->sequenceService = $sequenceService;
    }

    /**
     * Create a new CRM Lead with sequential lead number.
     */
    public function createLead(array $data): Lead
    {
        return DB::transaction(function () use ($data) {
            $leadNumber = $this->sequenceService->getNextNumber('APPLICATION'); // or LEAD sequence
            $data['lead_number'] = str_replace('APP-', 'LEAD-', $leadNumber);
            $data['status'] = $data['status'] ?? 'NEW';

            return Lead::create($data);
        });
    }

    /**
     * Log a follow-up activity for a lead.
     */
    public function logActivity(Lead $lead, array $activityData): LeadActivity
    {
        $activityData['lead_id'] = $lead->id;
        $activityData['employee_id'] = $activityData['employee_id'] ?? Auth::user()?->employee?->id;

        return LeadActivity::create($activityData);
    }
}
