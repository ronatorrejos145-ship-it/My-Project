<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoiceCancellationService
{
    public function __construct(
        protected FinancialReversalService $reversalService
    ) {}

    public function cancelInvoice(Invoice $invoice, string $reason, ?int $userId = null): Invoice
    {
        return DB::transaction(function () use ($invoice, $reason, $userId) {
            $invoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            if (in_array($invoice->status, ['CANCELLED', 'VOID'])) {
                return $invoice;
            }

            $oldStatus = $invoice->status;

            $invoice->update([
                'status' => 'CANCELLED',
                'cancelled_at' => now(),
                'cancelled_by' => $userId,
                'cancellation_reason' => $reason,
                'updated_by' => $userId,
            ]);

            // Reverse associated ledger transaction if posted
            $tx = $invoice->ledgerTransactions()->where('status', 'POSTED')->first();
            if ($tx) {
                $this->reversalService->reverseTransaction($tx, "Invoice cancelled: {$reason}", $userId);
            }

            AuditLogService::log(
                'CANCEL_INVOICE',
                'finance',
                $invoice,
                ['status' => $oldStatus],
                ['status' => 'CANCELLED', 'reason' => $reason]
            );

            return $invoice;
        });
    }
}
