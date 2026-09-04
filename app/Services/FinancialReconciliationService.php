<?php

namespace App\Services;

use App\Models\BillableCharge;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\LedgerTransaction;

class FinancialReconciliationService
{
    public function reconcileBillingToInvoices(): array
    {
        $mismatches = [];
        $uninvoicedCharges = BillableCharge::where('status', 'CHARGED')->get();

        foreach ($uninvoicedCharges as $charge) {
            $mismatches[] = [
                'type' => 'UNINVOICED_CHARGE',
                'charge_number' => $charge->charge_number,
                'customer_id' => $charge->customer_id,
                'amount' => $charge->total_amount,
                'description' => "Billable charge {$charge->charge_number} has status CHARGED but no invoice line.",
            ];
        }

        // Check for finalized invoices without ledger postings
        $finalizedInvoices = Invoice::where('status', 'FINALIZED')->get();
        foreach ($finalizedInvoices as $inv) {
            $hasLedger = LedgerTransaction::where('reference_type', 'App\\Models\\Invoice')
                ->where('reference_id', $inv->id)
                ->where('status', 'POSTED')
                ->exists();

            if (!$hasLedger) {
                $mismatches[] = [
                    'type' => 'MISSING_LEDGER_POSTING',
                    'invoice_number' => $inv->invoice_number,
                    'customer_id' => $inv->customer_id,
                    'amount' => $inv->total_amount,
                    'description' => "Finalized invoice {$inv->invoice_number} is missing posted ledger transaction.",
                ];
            }
        }

        return [
            'reconciled' => count($mismatches) === 0,
            'mismatch_count' => count($mismatches),
            'mismatches' => $mismatches,
        ];
    }
}
