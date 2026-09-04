<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\LedgerTransaction;
use Illuminate\Support\Facades\DB;

class LedgerPostingService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService,
        protected BalanceService $balanceService
    ) {}

    public function postInvoiceDebit(Invoice $invoice, ?int $userId = null): LedgerTransaction
    {
        return DB::transaction(function () use ($invoice, $userId) {
            $idempotencyKey = "TX_INV_" . $invoice->id;

            $existing = LedgerTransaction::where('reference_type', 'App\\Models\\Invoice')
                ->where('reference_id', $invoice->id)
                ->where('status', 'POSTED')
                ->first();

            if ($existing) {
                return $existing;
            }

            $txNum = $this->numberSequenceService->getNextNumber('LEDGER_TX');

            $amount = (float) $invoice->total_amount;

            $tx = LedgerTransaction::create([
                'transaction_number' => $txNum,
                'customer_id' => $invoice->customer_id,
                'service_account_id' => $invoice->service_account_id,
                'invoice_id' => $invoice->id,
                'transaction_type' => 'INVOICE',
                'transaction_date' => $invoice->invoice_date,
                'posting_date' => now(),
                'debit_amount' => $amount,
                'credit_amount' => 0.00,
                'net_amount' => $amount,
                'currency' => $invoice->currency ?? 'PHP',
                'reference_type' => 'App\\Models\\Invoice',
                'reference_id' => $invoice->id,
                'description' => "Invoice #{$invoice->invoice_number} generated",
                'status' => 'POSTED',
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            // Rebuild balance snapshot
            $this->balanceService->rebuildSnapshot($invoice->customer_id, $invoice->service_account_id);

            AuditLogService::log(
                'POST_LEDGER_DEBIT',
                'finance',
                $tx,
                null,
                $tx->toArray()
            );

            return $tx;
        });
    }
}
