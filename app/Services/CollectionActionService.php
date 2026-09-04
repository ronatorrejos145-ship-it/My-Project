<?php

namespace App\Services;

use App\Models\CollectionAccount;
use App\Models\CollectionAction;
use App\Models\Customer;
use App\Models\PaymentArrangement;
use App\Models\PaymentArrangementInstallment;
use App\Models\PromiseToPay;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CollectionActionService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService
    ) {}

    public function recordCollectionAction(
        CollectionAccount $account,
        string $actionType,
        ?string $notes = null,
        ?string $nextActionDate = null,
        ?int $collectorUserId = null
    ): CollectionAction {
        return DB::transaction(function () use ($account, $actionType, $notes, $nextActionDate, $collectorUserId) {
            $actNum = $this->numberSequenceService->getNextNumber('COLLECTION');

            $action = CollectionAction::create([
                'action_number' => $actNum,
                'collection_account_id' => $account->id,
                'customer_id' => $account->customer_id,
                'service_account_id' => $account->service_account_id,
                'action_type' => $actionType,
                'collector_user_id' => $collectorUserId,
                'action_at' => now(),
                'result_status' => 'COMPLETED',
                'notes' => $notes,
                'next_action_date' => $nextActionDate,
            ]);

            $account->update([
                'last_collection_action' => $actionType,
                'next_collection_action_date' => $nextActionDate,
            ]);

            AuditLogService::log('RECORD_COLLECTION_ACTION', 'finance', $action, null, $action->toArray());

            return $action;
        });
    }

    public function createPromiseToPay(
        Customer $customer,
        float $amount,
        string $promisedDate,
        ?string $notes = null,
        ?int $userId = null
    ): PromiseToPay {
        return DB::transaction(function () use ($customer, $amount, $promisedDate, $notes, $userId) {
            if ($amount <= 0) {
                throw new InvalidArgumentException("Promised amount must be greater than zero.");
            }

            $promiseNum = $this->numberSequenceService->getNextNumber('PROMISE');
            $collAccount = CollectionAccount::where('customer_id', $customer->id)->first();

            $promise = PromiseToPay::create([
                'promise_number' => $promiseNum,
                'customer_id' => $customer->id,
                'collection_account_id' => $collAccount?->id,
                'promised_amount' => round($amount, 2),
                'promised_date' => $promisedDate,
                'status' => 'ACTIVE',
                'created_by' => $userId,
                'notes' => $notes,
            ]);

            AuditLogService::log('CREATE_PROMISE_TO_PAY', 'finance', $promise, null, $promise->toArray());

            return $promise;
        });
    }

    public function createPaymentArrangement(
        Customer $customer,
        float $totalAmount,
        float $downPayment,
        int $installmentsCount,
        string $startDate,
        ?string $notes = null,
        ?int $userId = null
    ): PaymentArrangement {
        return DB::transaction(function () use ($customer, $totalAmount, $downPayment, $installmentsCount, $startDate, $notes, $userId) {
            if ($totalAmount <= 0 || $installmentsCount < 1) {
                throw new InvalidArgumentException("Invalid arrangement parameters.");
            }

            $arrNum = $this->numberSequenceService->getNextNumber('ARRANGEMENT');
            $remaining = round($totalAmount - $downPayment, 2);
            $installmentAmt = round($remaining / $installmentsCount, 2);

            $arrangement = PaymentArrangement::create([
                'arrangement_number' => $arrNum,
                'customer_id' => $customer->id,
                'total_amount' => round($totalAmount, 2),
                'down_payment_amount' => round($downPayment, 2),
                'installment_amount' => $installmentAmt,
                'installment_frequency' => 'MONTHLY',
                'total_installments' => $installmentsCount,
                'paid_installments' => 0,
                'start_date' => $startDate,
                'due_day_of_month' => (int)date('d', strtotime($startDate)),
                'remaining_balance' => $remaining,
                'status' => 'PENDING_APPROVAL',
                'created_by' => $userId,
                'notes' => $notes,
            ]);

            // Generate installment schedule
            $currDate = \Carbon\Carbon::parse($startDate);
            for ($i = 1; $i <= $installmentsCount; $i++) {
                PaymentArrangementInstallment::create([
                    'arrangement_id' => $arrangement->id,
                    'installment_number' => $i,
                    'due_date' => $currDate->copy()->addMonths($i - 1)->toDateString(),
                    'amount_due' => ($i === $installmentsCount) ? round($remaining - ($installmentAmt * ($installmentsCount - 1)), 2) : $installmentAmt,
                    'amount_paid' => 0.00,
                    'status' => 'PENDING',
                ]);
            }

            AuditLogService::log('CREATE_PAYMENT_ARRANGEMENT', 'finance', $arrangement, null, $arrangement->toArray());

            return $arrangement;
        });
    }

    public function approvePaymentArrangement(PaymentArrangement $arrangement, int $userId): PaymentArrangement
    {
        return DB::transaction(function () use ($arrangement, $userId) {
            if ($arrangement->status !== 'PENDING_APPROVAL') {
                throw new InvalidArgumentException("Arrangement {$arrangement->arrangement_number} is not pending approval.");
            }

            $arrangement->update([
                'status' => 'ACTIVE',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            return $arrangement;
        });
    }
}
