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
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            CompanySeeder::class,
            BranchSeeder::class,
            BarangaySeeder::class,
            ServiceAreaSeeder::class,
            PackageCategorySeeder::class,
            ServicePackageSeeder::class,
            ProductCatalogSeeder::class,
            LeadSeeder::class,
            CustomerSeeder::class,
            BillingProfileSeeder::class,
            SubscriptionSeeder::class,
            BillingSeeder::class,
            Phase14PaymentsSeeder::class,
            Phase15AdjustmentsSeeder::class,
            Phase16CollectionsSeeder::class,
            Phase17CustomerPortalSeeder::class,
            Phase18CustomerServiceSeeder::class,
            Phase19FieldServiceSeeder::class,
        ]);
    }
}
