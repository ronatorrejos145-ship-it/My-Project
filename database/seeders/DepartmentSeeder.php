<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Management', 'code' => 'MGMT', 'description' => 'Executive & General Management'],
            ['name' => 'Administration', 'code' => 'ADMIN', 'description' => 'Platform & Operations Administration'],
            ['name' => 'Finance', 'code' => 'FINANCE', 'description' => 'Financial Ledger, Billing & Collections'],
            ['name' => 'Customer Service', 'code' => 'CSR', 'description' => 'Customer Support & CRM Operations'],
            ['name' => 'Sales', 'code' => 'SALES', 'description' => 'Subscriber Acquisition & Packages'],
            ['name' => 'Technical', 'code' => 'TECH', 'description' => 'Field Service, Installation & Maintenance'],
            ['name' => 'NOC Operations', 'code' => 'NOC', 'description' => 'Network Operations & Monitoring'],
            ['name' => 'Warehouse', 'code' => 'WAREHOUSE', 'description' => 'Inventory, Assets & Tool Management'],
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'Personnel & Workforce Management'],
        ];

        foreach ($departments as $d) {
            Department::updateOrCreate(['code' => $d['code']], [
                'name' => $d['name'],
                'description' => $d['description'],
                'status' => 'ACTIVE',
            ]);
        }
    }
}
