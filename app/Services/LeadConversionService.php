<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Customer;
use App\Models\CustomerStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LeadConversionService
{
    protected NumberSequenceService $sequenceService;
    protected CustomerActivityService $activityService;

    public function __construct(NumberSequenceService $sequenceService, CustomerActivityService $activityService)
    {
        $this->sequenceService = $sequenceService;
        $this->activityService = $activityService;
    }

    /**
     * Atomically convert a lead into a customer record inside a DB transaction.
     */
    public function convertToCustomer(Lead $lead, array $customerOverrides = []): Customer
    {
        return DB::transaction(function () use ($lead, $customerOverrides) {
            $customerNumber = $this->sequenceService->getNextNumber('CUSTOMER');
            $accountNumber = $this->sequenceService->getNextNumber('ACCOUNT');

            $customerData = array_merge([
                'customer_number' => $customerNumber,
                'account_number' => $accountNumber,
                'customer_type' => 'RESIDENTIAL',
                'status' => 'PROSPECT',
                'contact_person' => $lead->name,
                'primary_phone' => $lead->mobile,
                'secondary_phone' => $lead->telephone,
                'email' => $lead->email,
                'branch_id' => $lead->branch_id,
                'assigned_employee_id' => $lead->assigned_employee_id,
                'referred_by_customer_id' => $lead->referral_customer_id,
                'acquisition_source' => $lead->source,
            ], $customerOverrides);

            $customer = Customer::create($customerData);

            // Update lead record
            $oldStatus = $lead->status;
            $lead->status = 'CONVERTED';
            $lead->converted_customer_id = $customer->id;
            $lead->save();

            // Log status history for customer & lead
            CustomerStatusHistory::create([
                'customer_id' => $customer->id,
                'old_status' => null,
                'new_status' => $customer->status,
                'reason' => "Converted from CRM Lead #{$lead->lead_number}",
                'changed_by' => Auth::id(),
                'source' => 'LEAD_CONVERSION',
            ]);

            $this->activityService->log(
                $customer->id,
                'LEAD_CONVERTED',
                "Converted from Lead #{$lead->lead_number}",
                "Lead {$lead->name} successfully converted to Customer #{$customerNumber}.",
                ['lead_id' => $lead->id, 'lead_number' => $lead->lead_number]
            );

            return $customer;
        });
    }
}
