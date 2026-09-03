<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\ServicePackageVersion;
use App\Models\PackageFeature;
use App\Models\BillingCycle;
use App\Models\Tax;
use App\Models\Branch;
use App\Models\ServiceArea;
use App\Models\AssetModel;
use App\Models\Promotion;
use App\Models\PackageEquipmentRequirement;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $monthly = BillingCycle::where('code', 'MONTHLY')->first();
        $vat = Tax::where('code', 'VAT12')->first();
        $branch = Branch::first();
        $serviceArea = ServiceArea::first();
        $onuModel = AssetModel::first();

        // Categories
        $catHome = ServiceCategory::firstOrCreate(['code' => 'CAT-HOME'], ['name' => 'Home Fiber Internet', 'category_type' => 'HOME', 'display_order' => 1]);
        $catBiz = ServiceCategory::firstOrCreate(['code' => 'CAT-BIZ'], ['name' => 'Business Fiber Internet', 'category_type' => 'BUSINESS', 'display_order' => 2]);

        // Features
        $featRouter = PackageFeature::firstOrCreate(['code' => 'FEAT-ROUTER'], ['name' => 'Dual Band WiFi 6 Router', 'feature_type' => 'BOOLEAN']);
        $featUnlim = PackageFeature::firstOrCreate(['code' => 'FEAT-UNLIMITED-DATA'], ['name' => 'Truly Unlimited Data No Cap', 'feature_type' => 'BOOLEAN']);
        $featStaticIp = PackageFeature::firstOrCreate(['code' => 'FEAT-STATIC-IP'], ['name' => 'Dedicated Static IP Address', 'feature_type' => 'BOOLEAN']);

        // Home Packages
        $pkg20 = ServicePackage::firstOrCreate(
            ['package_code' => 'HOME-20'],
            [
                'service_category_id' => $catHome->id,
                'name' => 'Home Fiber Plan 20 Mbps [DEMO]',
                'short_name' => 'Fiber 20M',
                'description' => 'Reliable fiber optic internet for casual browsing & streaming.',
                'package_type' => 'RESIDENTIAL',
                'technology' => 'FIBER',
                'download_speed' => 20,
                'upload_speed' => 20,
                'speed_guaranteed' => 10,
                'base_price' => 799.00,
                'installation_fee' => 1500.00,
                'billing_cycle_id' => $monthly?->id,
                'tax_id' => $vat?->id,
                'grace_period_days' => 3,
                'contract_period_months' => 24,
                'public_visibility' => true,
                'status' => 'ACTIVE',
            ]
        );

        ServicePackageVersion::firstOrCreate(
            ['package_id' => $pkg20->id, 'version_number' => 1],
            [
                'version_name' => 'Initial 20M Launch',
                'effective_from' => '2024-01-01 00:00:00',
                'price' => 799.00,
                'installation_fee' => 1500.00,
                'download_speed' => 20,
                'upload_speed' => 20,
                'billing_cycle_id' => $monthly?->id,
                'status' => 'ACTIVE',
                'change_reason' => 'Initial Catalog Release',
            ]
        );

        $pkg50 = ServicePackage::firstOrCreate(
            ['package_code' => 'HOME-50'],
            [
                'service_category_id' => $catHome->id,
                'name' => 'Home Fiber Plan 50 Mbps [DEMO]',
                'short_name' => 'Fiber 50M',
                'description' => 'Fast fiber internet for medium households & remote work.',
                'package_type' => 'RESIDENTIAL',
                'technology' => 'FIBER',
                'download_speed' => 50,
                'upload_speed' => 50,
                'base_price' => 1299.00,
                'installation_fee' => 1500.00,
                'billing_cycle_id' => $monthly?->id,
                'tax_id' => $vat?->id,
                'status' => 'ACTIVE',
            ]
        );

        ServicePackageVersion::firstOrCreate(
            ['package_id' => $pkg50->id, 'version_number' => 1],
            [
                'version_name' => 'Launch Price',
                'effective_from' => '2024-01-01 00:00:00',
                'price' => 1299.00,
                'installation_fee' => 1500.00,
                'download_speed' => 50,
                'upload_speed' => 50,
                'status' => 'ACTIVE',
                'change_reason' => 'Initial Catalog Release',
            ]
        );

        // Attach features & availability
        $pkg50->features()->syncWithoutDetaching([$featRouter->id, $featUnlim->id]);

        if ($branch) {
            $pkg50->branches()->syncWithoutDetaching([$branch->id]);
        }
        if ($serviceArea) {
            $pkg50->serviceAreas()->syncWithoutDetaching([$serviceArea->id]);
        }

        if ($onuModel) {
            PackageEquipmentRequirement::firstOrCreate(
                ['package_id' => $pkg50->id, 'asset_model_id' => $onuModel->id],
                ['quantity' => 1, 'is_required' => true, 'is_included' => true, 'notes' => '1x Dual Band GPON ONT Unit']
            );
        }

        // Promotions
        Promotion::firstOrCreate(
            ['code' => 'PROMO-FREE-INST'],
            [
                'name' => 'Free Installation Summer Promo [DEMO]',
                'promo_type' => 'FREE_INSTALLATION',
                'discount_amount' => 1500.00,
                'start_date' => '2024-01-01 00:00:00',
                'end_date' => '2025-12-31 23:59:59',
                'status' => 'ACTIVE',
            ]
        );
    }
}
