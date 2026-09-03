<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CustomerService
{
    protected NumberSequenceService $sequenceService;
    protected CustomerActivityService $activityService;

    public function __construct(NumberSequenceService $sequenceService, CustomerActivityService $activityService)
    {
        $this->sequenceService = $sequenceService;
        $this->activityService = $activityService;
    }

    /**
     * Create a new customer master record with sequential customer and account numbers.
     */
    public function createCustomer(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $customerNumber = $this->sequenceService->getNextNumber('CUSTOMER');
            $accountNumber = $this->sequenceService->getNextNumber('ACCOUNT');

            $data['customer_number'] = $customerNumber;
            $data['account_number'] = $accountNumber;
            $data['status'] = $data['status'] ?? 'PROSPECT';

            $customer = Customer::create($data);

            CustomerStatusHistory::create([
                'customer_id' => $customer->id,
                'old_status' => null,
                'new_status' => $customer->status,
                'reason' => 'Customer master record created.',
                'changed_by' => Auth::id(),
                'source' => 'REGISTRATION',
            ]);

            $this->activityService->log(
                $customer->id,
                'CUSTOMER_CREATED',
                "Customer Master Record Created",
                "Customer account #{$customerNumber} created with status '{$customer->status}'.",
                ['customer_number' => $customerNumber, 'account_number' => $accountNumber]
            );

            return $customer;
        });
    }
}
