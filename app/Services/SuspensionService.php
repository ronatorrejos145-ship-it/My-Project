<?php

namespace App\Services;

use App\Models\CollectionAccount;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\SuspensionExecution;
use App\Models\SuspensionExemption;
use App\Models\SuspensionRequest;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SuspensionService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService
    ) {}

    public function isEligibleForSuspension(Customer $customer): bool
    {
        $collAccount = CollectionAccount::where('customer_id', $customer->id)->first();
        if (!$collAccount) {
            return false;
        }

        // Check if exempt or hold active
        $hasActiveExemption = SuspensionExemption::where('customer_id', $customer->id)
            ->where('status', 'ACTIVE')
            ->where(function($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->toDateString());
            })
            ->exists();

        if ($hasActiveExemption || $collAccount->is_exempt_from_suspension) {
            return false;
        }

        return ($collAccount->days_overdue >= 15 && (float)$collAccount->overdue_amount >= 100.00);
    }

    public function requestSuspension(
        Customer $customer,
        string $reason = 'Automated Delinquency Suspension',
        ?Subscription $subscription = null,
        ?int $requestedBy = null,
        bool $autoApprove = true
    ): SuspensionRequest {
        return DB::transaction(function () use ($customer, $reason, $subscription, $requestedBy, $autoApprove) {
            $collAccount = CollectionAccount::where('customer_id', $customer->id)->first();
            $sub = $subscription ?? Subscription::where('customer_id', $customer->id)->where('status', 'ACTIVE')->first();

            $reqNum = $this->numberSequenceService->getNextNumber('SUSPENSION');

            $request = SuspensionRequest::create([
                'request_number' => $reqNum,
                'customer_id' => $customer->id,
                'service_account_id' => $sub?->service_account_id ?? $collAccount?->service_account_id,
                'subscription_id' => $sub?->id,
                'reason' => $reason,
                'delinquency_amount' => $collAccount?->overdue_amount ?? 0.00,
                'days_overdue' => $collAccount?->days_overdue ?? 0,
                'approval_status' => $autoApprove ? 'APPROVED' : 'PENDING_APPROVAL',
                'network_action_status' => 'PENDING',
                'requested_by' => $requestedBy,
                'approved_by' => $autoApprove ? $requestedBy : null,
                'approved_at' => $autoApprove ? now() : null,
            ]);

            if ($autoApprove) {
                $this->executeSuspension($request);
            }

            AuditLogService::log('REQUEST_SUSPENSION', 'finance', $request, null, $request->toArray());

            return $request;
        });
    }

    public function executeSuspension(SuspensionRequest $request): SuspensionExecution
    {
        return DB::transaction(function () use ($request) {
            if ($request->approval_status !== 'APPROVED') {
                throw new InvalidArgumentException("Suspension request {$request->request_number} is not approved.");
            }

            // Update Commercial Subscription State to SUSPENDED (Phase 11 integration)
            if ($request->subscription) {
                $request->subscription->update([
                    'status' => 'SUSPENDED',
                    'suspended_at' => now(),
                    'suspension_reason' => $request->reason,
                ]);
            }

            // Update Collection Account Status
            $collAccount = CollectionAccount::where('customer_id', $request->customer_id)->first();
            if ($collAccount) {
                $collAccount->update([
                    'delinquency_status' => 'SUSPENDED',
                    'suspended_at' => now(),
                ]);
            }

            // Record Provider Action Execution Contract
            $execution = SuspensionExecution::create([
                'suspension_request_id' => $request->id,
                'subscription_id' => $request->subscription_id,
                'action_type' => 'SUSPEND',
                'provider' => 'MANUAL', // PENDING Phase 21 Router/RADIUS Contract
                'status' => 'SUCCESS',
                'response_payload' => ['message' => 'Commercial status set to SUSPENDED. Network action queued for Phase 21 integration.'],
                'executed_at' => now(),
            ]);

            $request->update([
                'network_action_status' => 'COMPLETED',
                'executed_at' => now(),
                'result_notes' => 'Commercial suspension executed cleanly.',
            ]);

            return $execution;
        });
    }
}
