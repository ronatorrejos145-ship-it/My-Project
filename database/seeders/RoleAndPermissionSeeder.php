<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // User & Security Management
            ['name' => 'View Users', 'code' => 'users.view', 'module' => 'users'],
            ['name' => 'Create Users', 'code' => 'users.create', 'module' => 'users'],

            // Customer Management
            ['name' => 'View Customers', 'code' => 'customers.view', 'module' => 'customers'],
            ['name' => 'Create Customers', 'code' => 'customers.create', 'module' => 'customers'],

            // Technical Surveys (Phase 7)
            ['name' => 'View Technical Surveys', 'code' => 'technical_surveys.view', 'module' => 'surveys'],
            ['name' => 'Create Technical Surveys', 'code' => 'technical_surveys.create', 'module' => 'surveys'],
            ['name' => 'Assign Technical Surveys', 'code' => 'technical_surveys.assign', 'module' => 'surveys'],
            ['name' => 'Schedule Technical Surveys', 'code' => 'technical_surveys.schedule', 'module' => 'surveys'],
            ['name' => 'Execute & Submit Surveys', 'code' => 'technical_surveys.submit', 'module' => 'surveys'],
            ['name' => 'Review & Approve Surveys', 'code' => 'technical_surveys.review', 'module' => 'surveys'],

            // GIS & Location Intelligence
            ['name' => 'View GIS Operations Map', 'code' => 'gis.view', 'module' => 'gis'],
            ['name' => 'Manage Towers & Splitters', 'code' => 'gis.manage_towers', 'module' => 'gis'],

            // Online Applications & Serviceability
            ['name' => 'View Service Applications', 'code' => 'applications.view', 'module' => 'applications'],
            ['name' => 'Run Technical Serviceability Checks', 'code' => 'serviceability.check', 'module' => 'serviceability'],

            // Product Catalog
            ['name' => 'View Service Packages', 'code' => 'packages.view', 'module' => 'packages'],

            // CRM Leads
            ['name' => 'View CRM Leads', 'code' => 'leads.view', 'module' => 'leads'],

            // System Administration
            ['name' => 'Manage System Settings', 'code' => 'settings.manage', 'module' => 'settings'],
            ['name' => 'View Audit Logs', 'code' => 'audit.view', 'module' => 'audit'],
        ];

        $permissionModels = [];
        foreach ($permissions as $p) {
            $permissionModels[$p['code']] = Permission::updateOrCreate(['code' => $p['code']], [
                'name' => $p['name'],
                'module' => $p['module'],
            ]);
        }

        // Create Roles
        $roles = [
            'SUPER_ADMIN' => ['name' => 'Super Administrator', 'description' => 'Full unrestricted platform control'],
            'ADMIN' => ['name' => 'Administrator', 'description' => 'System administration and user management'],
            'MANAGER' => ['name' => 'Manager', 'description' => 'Executive operational overview'],
            'TECHNICAL_SUPERVISOR' => ['name' => 'Technical Supervisor', 'description' => 'Field crew dispatch and survey approval'],
            'TECHNICAL' => ['name' => 'Field Technician', 'description' => 'Field surveys and site inspections'],
            'CUSTOMER_SERVICE' => ['name' => 'Customer Service Rep', 'description' => 'CRM and support ticket management'],
            'SALES' => ['name' => 'Sales Agent', 'description' => 'Prospect registration and serviceability checking'],
        ];

        foreach ($roles as $code => $data) {
            $roleModel = Role::updateOrCreate(['code' => $code], [
                'name' => $data['name'],
                'description' => $data['description'],
            ]);

            if ($code === 'SUPER_ADMIN' || $code === 'ADMIN' || $code === 'MANAGER' || $code === 'TECHNICAL_SUPERVISOR') {
                $roleModel->permissions()->sync(array_column($permissionModels, 'id'));
            } elseif ($code === 'TECHNICAL') {
                $roleModel->permissions()->sync([
                    $permissionModels['technical_surveys.view']->id,
                    $permissionModels['technical_surveys.submit']->id,
                    $permissionModels['gis.view']->id,
                    $permissionModels['customers.view']->id,
                ]);
            } else {
                $roleModel->permissions()->sync([
                    $permissionModels['technical_surveys.view']->id,
                    $permissionModels['gis.view']->id,
                    $permissionModels['customers.view']->id,
                ]);
            }
        }
    }
}
