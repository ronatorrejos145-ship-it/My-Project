<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $adminDept = Department::where('code', 'ADMIN')->first();
        $techDept = Department::where('code', 'TECH')->first();
        $finDept = Department::where('code', 'FIN')->first();
        $whDept = Department::where('code', 'WH')->first();
        $csDept = Department::where('code', 'CS')->first();

        $positions = [
            ['code' => 'GM', 'name' => 'General Manager', 'department_id' => $adminDept?->id ?? 1],
            ['code' => 'BM', 'name' => 'Branch Manager', 'department_id' => $adminDept?->id ?? 1],
            ['code' => 'TECH_SUP', 'name' => 'Technical Supervisor', 'department_id' => $techDept?->id ?? 1],
            ['code' => 'FIELD_TECH', 'name' => 'Field Technician', 'department_id' => $techDept?->id ?? 1],
            ['code' => 'NOC_ENG', 'name' => 'NOC Engineer', 'department_id' => $techDept?->id ?? 1],
            ['code' => 'CASHIER', 'name' => 'Branch Cashier', 'department_id' => $finDept?->id ?? 1],
            ['code' => 'ACCOUNTANT', 'name' => 'Senior Accountant', 'department_id' => $finDept?->id ?? 1],
            ['code' => 'WH_OFFICER', 'name' => 'Warehouse Officer', 'department_id' => $whDept?->id ?? 1],
            ['code' => 'CSR', 'name' => 'Customer Service Representative', 'department_id' => $csDept?->id ?? 1],
        ];

        foreach ($positions as $p) {
            Position::firstOrCreate(['code' => $p['code']], $p);
        }
    }
}
