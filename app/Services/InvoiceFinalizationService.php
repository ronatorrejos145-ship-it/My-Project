<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoiceFinalizationService
{
    public function __construct(
        protected LedgerPostingService $ledgerPostingService
    ) {}

    public function finalizeInvoice(Invoice $invoice, ?int $userId = null): Invoice
    {
        return DB::transaction(function () use ($invoice, $userId) {
            /** @var Invoice $invoice */
            $invoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            if ($invoice->status === 'FINALIZED') {
                return $invoice;
            }

            if (in_array($invoice->status, ['CANCELLED', 'VOID'])) {
                throw new InvalidArgumentException("Cannot finalize invoice {$invoice->invoice_number} with status {$invoice->status}.");
            }

            $invoice->update([
                'status' => 'FINALIZED',
                'finalized_at' => now(),
                'updated_by' => $userId,
            ]);

            // Post financial debit to authoritative ledger
            $this->ledgerPostingService->postInvoiceDebit($invoice, $userId);

            AuditLogService::log(
                'FINALIZE_INVOICE',
                'finance',
                $invoice,
                ['status' => 'DRAFT'],
                ['status' => 'FINALIZED', 'finalized_at' => now()->toIso8601String()]
            );

            return $invoice;
        });
    }
}
