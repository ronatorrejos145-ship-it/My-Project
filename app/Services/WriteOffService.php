<?php

namespace App\Services;

use App\Models\CollectionAccount;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\LedgerTransaction;
use App\Models\WriteOffRequest;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WriteOffService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService
    ) {}

    public function createWriteOffRequest(
        Customer $customer,
        float $amount,
        string $reason,
        ?Invoice $invoice = null,
        ?int $userId = null
    ): WriteOffRequest {
        return DB::transaction(function () use ($customer, $amount, $reason, $invoice, $userId) {
            if ($amount <= 0) {
                throw new InvalidArgumentException("Write-off amount must be greater than zero.");
            }

            $writeOffNum = $this->numberSequenceService->getNextNumber('WRITEOFF');

            $request = WriteOffRequest::create([
                'write_off_number' => $writeOffNum,
                'customer_id' => $customer->id,
                'service_account_id' => $invoice?->service_account_id,
                'invoice_id' => $invoice?->id,
                'amount' => round($amount, 2),
                'reason' => $reason,
                'status' => 'PENDING_APPROVAL',
                'requested_by' => $userId,
            ]);

            AuditLogService::log('CREATE_WRITE_OFF_REQUEST', 'finance', $request, null, $request->toArray());

            return $request;
        });
    }

    public function approveAndPostWriteOff(WriteOffRequest $request, int $userId): WriteOffRequest
    {
        return DB::transaction(function () use ($request, $userId) {
            if ($request->status !== 'PENDING_APPROVAL' && $request->status !== 'REQUESTED') {
                throw new InvalidArgumentException("Write-off request {$request->write_off_number} is not pending approval.");
            }

            $ledgerTxNum = $this->numberSequenceService->getNextNumber('LEDGER');
            $customer = $request->customer;

            // Reduce customer balance by write-off amount (CREDIT transaction)
            $runningBalance = round((float)$customer->current_balance - (float)$request->amount, 2);
            $customer->update(['current_balance' => $runningBalance]);

            $ledgerTx = LedgerTransaction::create([
                'transaction_number' => $ledgerTxNum,
                'customer_id' => $request->customer_id,
                'service_account_id' => $request->service_account_id,
                'invoice_id' => $request->invoice_id,
                'transaction_type' => 'WRITE_OFF',
                'transaction_date' => now()->toDateString(),
                'posted_at' => now(),
                'amount' => $request->amount,
                'debit_credit' => 'CREDIT',
                'running_balance' => $runningBalance,
                'description' => "Bad Debt Write-Off {$request->write_off_number}: {$request->reason}",
                'is_posted' => true,
                'posted_by' => $userId,
            ]);

            $request->update([
                'status' => 'POSTED',
                'approved_by' => $userId,
                'approved_at' => now(),
                'posted_at' => now(),
                'ledger_transaction_id' => $ledgerTx->id,
            ]);

            // Update Collection Account Status
            $collAccount = CollectionAccount::where('customer_id', $request->customer_id)->first();
            if ($collAccount) {
                $collAccount->update([
                    'delinquency_status' => 'WRITTEN_OFF',
                    'overdue_amount' => 0.00,
                ]);
            }

            AuditLogService::log('APPROVE_WRITE_OFF', 'finance', $request, null, $request->toArray());

            return $request;
        });
    }
}
