<?php

namespace App\Services;

use App\Models\CollectionAccount;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\ReconnectionExecution;
use App\Models\ReconnectionRequest;
use App\Models\Subscription;
use App\Models\SuspensionRequest;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReconnectionService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService,
        protected ChargeGenerationService $chargeService
    ) {}

    public function requestReconnection(
        Customer $customer,
        ?Payment $payment = null,
        float $reconnectionFee = 0.00,
        bool $feeWaived = false,
        ?int $waivedBy = null,
        ?int $requestedBy = null,
        bool $autoApprove = true
    ): ReconnectionRequest {
        return DB::transaction(function () use ($customer, $payment, $reconnectionFee, $feeWaived, $waivedBy, $requestedBy, $autoApprove) {
            $collAccount = CollectionAccount::where('customer_id', $customer->id)->first();
            $sub = Subscription::where('customer_id', $customer->id)->where('status', 'SUSPENDED')->first();
            $lastSuspension = SuspensionRequest::where('customer_id', $customer->id)->orderBy('created_at', 'desc')->first();

            $reqNum = $this->numberSequenceService->getNextNumber('RECONNECTION');
            $remaining = $collAccount?->overdue_amount ?? 0.00;

            $request = ReconnectionRequest::create([
                'request_number' => $reqNum,
                'customer_id' => $customer->id,
                'service_account_id' => $sub?->service_account_id ?? $collAccount?->service_account_id,
                'subscription_id' => $sub?->id,
                'suspension_request_id' => $lastSuspension?->id,
                'payment_id' => $payment?->id,
                'amount_paid' => $payment?->amount ?? 0.00,
                'amount_remaining' => $remaining,
                'reconnection_fee' => round($reconnectionFee, 2),
                'reconnection_fee_waived' => $feeWaived,
                'waived_by' => $feeWaived ? $waivedBy : null,
                'approval_status' => $autoApprove ? 'APPROVED' : 'PENDING_APPROVAL',
                'network_action_status' => 'PENDING',
                'requested_by' => $requestedBy,
                'approved_by' => $autoApprove ? $requestedBy : null,
                'approved_at' => $autoApprove ? now() : null,
            ]);

            if ($autoApprove) {
                $this->executeReconnection($request);
            }

            AuditLogService::log('REQUEST_RECONNECTION', 'finance', $request, null, $request->toArray());

            return $request;
        });
    }

    public function executeReconnection(ReconnectionRequest $request): ReconnectionExecution
    {
        return DB::transaction(function () use ($request) {
            if ($request->approval_status !== 'APPROVED') {
                throw new InvalidArgumentException("Reconnection request {$request->request_number} is not approved.");
            }

            // Restore Subscription to ACTIVE (Phase 11 integration)
            if ($request->subscription) {
                $request->subscription->update([
                    'status' => 'ACTIVE',
                    'suspended_at' => null,
                    'suspension_reason' => null,
                ]);
            }

            // Update Collection Account Status
            $collAccount = CollectionAccount::where('customer_id', $request->customer_id)->first();
            if ($collAccount) {
                $collAccount->update([
                    'delinquency_status' => 'CURRENT',
                    'reconnected_at' => now(),
                    'overdue_amount' => 0.00,
                    'days_overdue' => 0,
                ]);
            }

            // Record Provider Execution Contract
            $execution = ReconnectionExecution::create([
                'reconnection_request_id' => $request->id,
                'subscription_id' => $request->subscription_id,
                'action_type' => 'RECONNECT',
                'provider' => 'MANUAL',
                'status' => 'SUCCESS',
                'response_payload' => ['message' => 'Commercial status restored to ACTIVE. Network restoration queued for Phase 21 integration.'],
                'executed_at' => now(),
            ]);

            $request->update([
                'network_action_status' => 'COMPLETED',
                'executed_at' => now(),
            ]);

            return $execution;
        });
    }
}
