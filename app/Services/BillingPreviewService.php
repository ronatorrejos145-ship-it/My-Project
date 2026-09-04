<?php

namespace App\Services;

use App\Models\ServiceAccount;
use App\Models\Subscription;

class BillingPreviewService
{
    public function __construct(
        protected ProrationCalculator $prorationCalculator,
        protected TaxCalculationService $taxCalculationService
    ) {}

    public function generateCustomerPreview(
        ServiceAccount $serviceAccount,
        string $previewDate,
        ?Subscription $targetSubscription = null
    ): array {
        $subscription = $targetSubscription ?? $serviceAccount->currentSubscription;
        if (!$subscription) {
            return ['error' => 'No subscription found for preview.'];
        }

        $basePrice = (float) $subscription->monthly_price_snapshot;
        $profile = $serviceAccount->billingProfile;
        $tax = $profile?->tax;

        $taxCalc = $this->taxCalculationService->calculateTax($basePrice, $tax);

        return [
            'service_account_number' => $serviceAccount->account_number,
            'customer_name' => $serviceAccount->customer->full_name ?? 'N/A',
            'package_name' => $subscription->package_name_snapshot,
            'preview_date' => $previewDate,
            'estimated_subtotal' => $taxCalc['subtotal'],
            'estimated_tax' => $taxCalc['tax_amount'],
            'estimated_total' => $taxCalc['total_amount'],
            'currency' => $profile?->currency ?? 'PHP',
            'disclaimer' => 'PREVIEW ESTIMATE ONLY - NOT AN AUTHORITATIVE FINANCIAL INVOICE',
        ];
    }
}
