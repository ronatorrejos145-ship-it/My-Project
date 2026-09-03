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
        ]);
    }
}
