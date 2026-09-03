<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class CompanyAndBranchSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['code' => 'DEMO-ISP'],
            [
                'legal_name' => 'Apex Broadband Communications Philippines Inc. [DEMO]',
                'trade_name' => 'Apex Fiber Internet',
                'registration_number' => 'CS202300001',
                'tax_identifier' => '009-876-543-000',
                'email' => 'contact@apexfiber.demo',
                'phone' => '+63 2 8123 4567',
                'website' => 'https://apexfiber.demo',
                'address' => '100 Telecom Tower, EDSA, Quezon City, Metro Manila',
                'status' => 'ACTIVE',
            ]
        );

        Branch::firstOrCreate(
            ['code' => 'HQ-MNL', 'company_id' => $company->id],
            [
                'name' => 'Metro Manila Central Hub',
                'branch_type' => 'HEAD_OFFICE',
                'phone' => '+63 2 8123 4568',
                'email' => 'manila@apexfiber.demo',
                'address' => '100 Telecom Tower, EDSA, Quezon City',
                'latitude' => 14.6507000,
                'longitude' => 121.0300000,
                'status' => 'ACTIVE',
            ]
        );

        Branch::firstOrCreate(
            ['code' => 'BR-CEB', 'company_id' => $company->id],
            [
                'name' => 'Cebu Regional Hub',
                'branch_type' => 'BRANCH',
                'phone' => '+63 32 234 5678',
                'email' => 'cebu@apexfiber.demo',
                'address' => 'IT Park, Lahug, Cebu City',
                'latitude' => 10.3280000,
                'longitude' => 123.9060000,
                'status' => 'ACTIVE',
            ]
        );
    }
}
