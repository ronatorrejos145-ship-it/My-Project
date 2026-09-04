<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CustomerStatusService
{
    protected CustomerActivityService $activityService;

    public function __construct(CustomerActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Atomically transition a customer's status, logging immutable history and timeline entry.
     */
    public function transition(Customer $customer, string $newStatus, string $reason, ?string $notes = null, string $source = 'MANUAL'): Customer
    {
        return DB::transaction(function () use ($customer, $newStatus, $reason, $notes, $source) {
            $oldStatus = $customer->status;

            if ($oldStatus === $newStatus) {
                return $customer;
            }

            $customer->status = $newStatus;
            $customer->save();

            CustomerStatusHistory::create([
                'customer_id' => $customer->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $reason,
                'notes' => $notes,
                'changed_by' => Auth::id(),
                'source' => $source,
                'changed_at' => now(),
            ]);

            $this->activityService->log(
                $customer->id,
                'STATUS_CHANGED',
                "Status changed to {$newStatus}",
                "Status transitioned from {$oldStatus} to {$newStatus}. Reason: {$reason}",
                ['old_status' => $oldStatus, 'new_status' => $newStatus, 'reason' => $reason]
            );

            return $customer;
        });
    }
}
