<?php

namespace App\Services;

use App\Models\BillableCharge;
use App\Models\BillingPeriod;
use App\Models\BillingProfile;
use App\Models\BillingRun;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class ChargeGenerationService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService,
        protected ProrationCalculator $prorationCalculator,
        protected TaxCalculationService $taxCalculationService
    ) {}

    public function generateRecurringCharge(
        BillingProfile $billingProfile,
        BillingPeriod $billingPeriod,
        Subscription $subscription,
        ?BillingRun $billingRun = null,
        ?int $userId = null
    ): BillableCharge {
        return DB::transaction(function () use ($billingProfile, $billingPeriod, $subscription, $billingRun, $userId) {
            $basePrice = (float) $subscription->monthly_price_snapshot;
            $package = $subscription->package;
            $packageVersion = $subscription->packageVersion;

            $taxCalc = $this->taxCalculationService->calculateTax($basePrice, $billingProfile->tax);

            $idempotencyKey = "CHG_REC_" . md5("{$billingProfile->id}_{$subscription->id}_{$billingPeriod->period_start}_{$billingPeriod->period_end}_{$basePrice}");

            $existing = BillableCharge::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            $chargeNum = $this->numberSequenceService->getNextNumber('BILLABLE_CHARGE');

            $charge = BillableCharge::create([
                'charge_number' => $chargeNum,
                'billing_run_id' => $billingRun?->id,
                'billing_period_id' => $billingPeriod->id,
                'billing_profile_id' => $billingProfile->id,
                'customer_id' => $subscription->customer_id,
                'service_account_id' => $subscription->service_account_id,
                'subscription_id' => $subscription->id,
                'package_id' => $package?->id,
                'package_version_id' => $packageVersion?->id,
                'charge_type' => 'RECURRING',
                'source_type' => 'App\\Models\\Subscription',
                'source_id' => $subscription->id,
                'description' => "Recurring Subscription Fee: {$subscription->package_name_snapshot} ({$billingPeriod->period_start->format('Y-m-d')} to {$billingPeriod->period_end->format('Y-m-d')})",
                'quantity' => 1.00,
                'unit_price' => $basePrice,
                'subtotal' => $taxCalc['subtotal'],
                'discount_amount' => 0.00,
                'taxable_amount' => $taxCalc['subtotal'],
                'tax_amount' => $taxCalc['tax_amount'],
                'total_amount' => $taxCalc['total_amount'],
                'currency' => $billingProfile->currency,
                'service_period_start' => $billingPeriod->period_start,
                'service_period_end' => $billingPeriod->period_end,
                'effective_date' => $billingPeriod->billing_date,
                'status' => 'CHARGED',
                'idempotency_key' => $idempotencyKey,
                'calculation_snapshot' => [
                    'package_name' => $subscription->package_name_snapshot,
                    'download_speed' => $subscription->download_speed_snapshot,
                    'upload_speed' => $subscription->upload_speed_snapshot,
                    'monthly_price' => $basePrice,
                    'tax_calculation' => $taxCalc,
                ],
                'generated_at' => now(),
                'created_by' => $userId,
            ]);

            AuditLogService::log(
                'GENERATE_RECURRING_CHARGE',
                'billing',
                $charge,
                null,
                $charge->toArray()
            );

            return $charge;
        });
    }
}
