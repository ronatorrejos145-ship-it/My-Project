<?php

namespace App\Services;

use App\Models\BillableCharge;
use App\Models\BillingPeriod;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\ServiceAccount;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoiceGenerationService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService
    ) {}

    public function generateForServiceAccount(
        ServiceAccount $serviceAccount,
        ?BillingPeriod $billingPeriod = null,
        ?int $userId = null
    ): Invoice {
        return DB::transaction(function () use ($serviceAccount, $billingPeriod, $userId) {
            $query = BillableCharge::where('service_account_id', $serviceAccount->id)
                ->where('status', 'CHARGED');

            if ($billingPeriod) {
                $query->where('billing_period_id', $billingPeriod->id);
            }

            $charges = $query->lockForUpdate()->get();

            if ($charges->isEmpty()) {
                throw new InvalidArgumentException("No uninvoiced billable charges found for Service Account {$serviceAccount->account_number}.");
            }

            $invNum = $this->numberSequenceService->getNextNumber('INVOICE');

            $subtotal = $charges->sum('subtotal');
            $discount = $charges->sum('discount_amount');
            $taxable = $charges->sum('taxable_amount');
            $tax = $charges->sum('tax_amount');
            $total = $charges->sum('total_amount');

            $profile = $serviceAccount->billingProfile;
            $dueDate = now()->addDays($profile?->due_days ?? 15)->toDateString();
            $graceDate = now()->addDays(($profile?->due_days ?? 15) + ($profile?->grace_days ?? 3))->toDateString();

            $invoice = Invoice::create([
                'invoice_number' => $invNum,
                'customer_id' => $serviceAccount->customer_id,
                'service_account_id' => $serviceAccount->id,
                'billing_period_id' => $billingPeriod?->id,
                'billing_run_id' => $charges->first()?->billing_run_id,
                'invoice_date' => now()->toDateString(),
                'period_start' => $billingPeriod?->period_start ?? $charges->min('service_period_start'),
                'period_end' => $billingPeriod?->period_end ?? $charges->max('service_period_end'),
                'due_date' => $dueDate,
                'grace_date' => $graceDate,
                'currency' => $profile?->currency ?? 'PHP',
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'taxable_amount' => $taxable,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'amount_paid' => 0.00,
                'amount_due' => $total,
                'status' => 'DRAFT',
                'notes' => 'Generated from billable charges.',
                'terms' => 'Please pay on or before the due date to avoid service disconnection.',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            foreach ($charges as $charge) {
                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'charge_id' => $charge->id,
                    'charge_type' => $charge->charge_type,
                    'description' => $charge->description,
                    'quantity' => $charge->quantity,
                    'unit_price' => $charge->unit_price,
                    'subtotal' => $charge->subtotal,
                    'discount_amount' => $charge->discount_amount,
                    'taxable_amount' => $charge->taxable_amount,
                    'tax_amount' => $charge->tax_amount,
                    'total_amount' => $charge->total_amount,
                    'service_period_start' => $charge->service_period_start,
                    'service_period_end' => $charge->service_period_end,
                    'package_id' => $charge->package_id,
                    'package_version_id' => $charge->package_version_id,
                    'source_type' => $charge->source_type,
                    'source_id' => $charge->source_id,
                    'metadata' => $charge->calculation_snapshot,
                ]);

                $charge->update(['status' => 'INVOICED']);
            }

            AuditLogService::log(
                'GENERATE_INVOICE',
                'finance',
                $invoice,
                null,
                $invoice->toArray()
            );

            return $invoice;
        });
    }
}
