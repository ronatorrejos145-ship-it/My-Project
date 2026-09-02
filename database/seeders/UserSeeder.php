<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin Account
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@isp.example.com'],
            [
                'name' => 'Super Administrator',
                'phone' => '+639170000000',
                'password' => Hash::make('AdminSecret123!'),
                'status' => UserStatus::ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        $superAdminRole = Role::where('code', 'SUPER_ADMIN')->first();
        if ($superAdminRole) {
            $superAdmin->roles()->sync([$superAdminRole->id]);
        }

        // 2. Sample Employee User & Employee Record
        $techUser = User::updateOrCreate(
            ['email' => 'tech@isp.example.com'],
            [
                'name' => 'Juan Tech',
                'phone' => '+639171111111',
                'password' => Hash::make('TechSecret123!'),
                'status' => UserStatus::ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        $techRole = Role::where('code', 'TECHNICIAN')->first();
        if ($techRole) {
            $techUser->roles()->sync([$techRole->id]);
        }

        $techDept = Department::where('code', 'TECH')->first();
        if ($techDept) {
            Employee::updateOrCreate(
                ['user_id' => $techUser->id],
                [
                    'employee_number' => 'EMP-1001',
                    'department_id' => $techDept->id,
                    'position' => 'Senior Field Technician',
                    'employment_status' => 'ACTIVE',
                    'hire_date' => now()->subYear(),
                    'phone' => '+639171111111',
                ]
            );
        }

        // 3. Sample Customer User & Customer Record
        $custUser = User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Maria Customer',
                'phone' => '+639172222222',
                'password' => Hash::make('CustomerSecret123!'),
                'status' => UserStatus::ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        $custRole = Role::where('code', 'CUSTOMER')->first();
        if ($custRole) {
            $custUser->roles()->sync([$custRole->id]);
        }

        Customer::updateOrCreate(
            ['customer_number' => 'CUST-10001'],
            [
                'account_number' => 'ACC-10001',
                'user_id' => $custUser->id,
                'customer_type' => 'RESIDENTIAL',
                'status' => 'ACTIVE',
                'contact_person' => 'Maria Customer',
                'primary_phone' => '+639172222222',
                'email' => 'customer@example.com',
                'installation_address' => '123 Fiber St, Barangay San Jose, Quezon City',
                'billing_address' => '123 Fiber St, Barangay San Jose, Quezon City',
                'current_balance' => 0.00,
            ]
        );
    }
}
