<?php

namespace Database\Seeders;

use App\Models\BillingCycle;
use App\Models\Tax;
use App\Models\PaymentMethod;
use App\Models\ServicePackage;
use App\Models\ServicePackageVersion;
use App\Models\PackageFeature;
use App\Models\Discount;
use Illuminate\Database\Seeder;

class PackageAndBillingMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Billing Cycles
        $monthly = BillingCycle::firstOrCreate(['code' => 'MONTHLY'], ['name' => 'Monthly Billing', 'interval' => 1, 'interval_unit' => 'MONTH']);
        BillingCycle::firstOrCreate(['code' => 'DAILY'], ['name' => 'Daily Prepaid', 'interval' => 1, 'interval_unit' => 'DAY']);
        BillingCycle::firstOrCreate(['code' => 'WEEKLY'], ['name' => 'Weekly Prepaid', 'interval' => 1, 'interval_unit' => 'WEEK']);

        // Taxes
        $vat = Tax::firstOrCreate(
            ['code' => 'VAT12'],
            ['name' => '12% Value Added Tax', 'rate' => 12.0000, 'tax_type' => 'PERCENTAGE', 'is_inclusive' => true, 'status' => 'ACTIVE']
        );

        // Payment Methods
        PaymentMethod::firstOrCreate(['code' => 'CASH'], ['name' => 'Cash Payment', 'method_type' => 'CASH', 'requires_reference' => false]);
        PaymentMethod::firstOrCreate(['code' => 'GCASH'], ['name' => 'GCash Mobile Wallet', 'method_type' => 'GCASH', 'provider' => 'GCash', 'requires_reference' => true]);
        PaymentMethod::firstOrCreate(['code' => 'MAYA'], ['name' => 'Maya Mobile Wallet', 'method_type' => 'MAYA', 'provider' => 'Maya', 'requires_reference' => true]);
        PaymentMethod::firstOrCreate(['code' => 'BANK_TRANSFER'], ['name' => 'Bank Direct Deposit / Transfer', 'method_type' => 'BANK_TRANSFER', 'requires_reference' => true]);

        // Features
        $featRouter = PackageFeature::firstOrCreate(['code' => 'FEAT-ROUTER-INC'], ['name' => 'Dual-Band Router Included']);
        $featUnlim = PackageFeature::firstOrCreate(['code' => 'FEAT-UNLIMITED'], ['name' => 'Truly Unlimited Data (No Cap)']);

        // Packages
        $pkg35 = ServicePackage::firstOrCreate(
            ['package_code' => 'PKG-RES-35M'],
            [
                'name' => 'Fiber Plan 35 Mbps [DEMO]',
                'description' => 'Fast fiber internet for small households.',
                'package_type' => 'RESIDENTIAL',
                'download_speed' => 35,
                'upload_speed' => 35,
                'speed_unit' => 'Mbps',
                'base_price' => 999.00,
                'installation_fee' => 1500.00,
                'activation_fee' => 0.00,
                'deposit_amount' => 0.00,
                'billing_cycle_id' => $monthly->id,
                'tax_id' => $vat->id,
                'grace_period_days' => 3,
                'contract_period_months' => 24,
                'status' => 'ACTIVE',
            ]
        );

        // Versioning foundation
        ServicePackageVersion::firstOrCreate(
            ['package_id' => $pkg35->id, 'version_number' => 1],
            [
                'effective_from' => '2024-01-01 00:00:00',
                'price' => 999.00,
                'installation_fee' => 1500.00,
                'activation_fee' => 0.00,
                'deposit_amount' => 0.00,
                'download_speed' => 35,
                'upload_speed' => 35,
                'speed_unit' => 'Mbps',
                'billing_cycle_id' => $monthly->id,
                'status' => 'ACTIVE',
                'change_reason' => 'Initial Version Release',
            ]
        );

        $pkg35->features()->syncWithoutDetaching([
            $featRouter->id => ['feature_value' => 'Yes'],
            $featUnlim->id => ['feature_value' => 'Unlimited'],
        ]);

        // Discount Promo
        Discount::firstOrCreate(
            ['code' => 'PROMO-SUMMER500'],
            [
                'name' => 'Summer Installation Discount (₱500 OFF)',
                'discount_type' => 'FIXED',
                'value' => 500.00,
                'start_date' => '2024-01-01 00:00:00',
                'end_date' => '2025-12-31 23:59:59',
                'usage_limit' => 500,
                'status' => 'ACTIVE',
            ]
        );
    }
}
