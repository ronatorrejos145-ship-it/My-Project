<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            // Phase 2 Master Data Seeders
            CompanyAndBranchSeeder::class,
            PositionSeeder::class,
            GeographySeeder::class,
            ServiceAreaSeeder::class,
            NetworkMasterDataSeeder::class,
            AssetAndToolMasterDataSeeder::class,
            WarehouseMasterDataSeeder::class,
            PackageAndBillingMasterDataSeeder::class,
            OperationsAndFinanceMasterDataSeeder::class,
            NumberSequenceSeeder::class,
            // Phase 3 CRM & Lead Seeders
            CustomerCRMAndLeadSeeder::class,
            // Phase 4 Product Catalog Seeder
            ProductCatalogSeeder::class,
            // Phase 5 Online Application & Serviceability Seeder
            ServiceApplicationSeeder::class,
            // Phase 6 GIS Infrastructure Seeder
            GisInfrastructureSeeder::class,
            // Phase 7 Technical Survey Seeder
            TechnicalSurveySeeder::class,
            // Phase 8 Installation Management Seeders
            InstallationChecklistTemplateSeeder::class,
            InstallationWorkOrderSeeder::class,
            // Phase 11 Subscriber & Subscription Seeder
            SubscriberSeeder::class,
            // Phase 12 Billing Engine Seeder
            BillingEngineSeeder::class,
            // Phase 13 Invoice & Ledger Seeder
            InvoiceAndLedgerSeeder::class,
            // Phase 14 Payment Management Seeder
            PaymentManagementSeeder::class,
            // Phase 15 Financial Adjustments Seeder
            Phase15FinancialAdjustmentSeeder::class,
            // Phase 16 Collections & Suspension Seeder
            Phase16CollectionsSeeder::class,
        ]);
    }
}
