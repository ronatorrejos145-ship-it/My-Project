<?php

namespace App\Services;

use App\Models\FinancialReversal;
use App\Models\LedgerTransaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FinancialReversalService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService,
        protected BalanceService $balanceService
    ) {}

    public function reverseTransaction(LedgerTransaction $originalTx, string $reason, ?int $userId = null): FinancialReversal
    {
        return DB::transaction(function () use ($originalTx, $reason, $userId) {
            $originalTx = LedgerTransaction::where('id', $originalTx->id)->lockForUpdate()->firstOrFail();

            if ($originalTx->status === 'REVERSED') {
                throw new InvalidArgumentException("Transaction {$originalTx->transaction_number} is already reversed.");
            }

            $reversalTxNum = $this->numberSequenceService->getNextNumber('LEDGER_TX');

            // Swap debits and credits
            $debit = (float) $originalTx->credit_amount;
            $credit = (float) $originalTx->debit_amount;
            $net = $debit - $credit;

            $reversalTx = LedgerTransaction::create([
                'transaction_number' => $reversalTxNum,
                'customer_id' => $originalTx->customer_id,
                'service_account_id' => $originalTx->service_account_id,
                'invoice_id' => $originalTx->invoice_id,
                'transaction_type' => 'REVERSAL',
                'transaction_date' => now()->toDateString(),
                'posting_date' => now(),
                'debit_amount' => $debit,
                'credit_amount' => $credit,
                'net_amount' => $net,
                'currency' => $originalTx->currency,
                'reference_type' => 'App\\Models\\LedgerTransaction',
                'reference_id' => $originalTx->id,
                'description' => "Reversal of TX #{$originalTx->transaction_number}: {$reason}",
                'status' => 'POSTED',
                'reversal_of_id' => $originalTx->id,
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            $originalTx->update(['status' => 'REVERSED']);

            $revNum = $this->numberSequenceService->getNextNumber('REVERSAL');
            $reversalLog = FinancialReversal::create([
                'reversal_number' => $revNum,
                'original_transaction_id' => $originalTx->id,
                'reversal_transaction_id' => $reversalTx->id,
                'reason' => $reason,
                'amount' => abs($net),
                'reversed_by' => $userId,
                'reversed_at' => now(),
            ]);

            // Rebuild balance snapshot
            $this->balanceService->rebuildSnapshot($originalTx->customer_id, $originalTx->service_account_id);

            AuditLogService::log(
                'REVERSE_LEDGER_TRANSACTION',
                'finance',
                $reversalTx,
                ['status' => 'POSTED'],
                ['status' => 'REVERSED', 'reason' => $reason]
            );

            return $reversalLog;
        });
    }
}
