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

            // GIS & Location Intelligence (Phase 6)
            ['name' => 'View GIS Operations Map', 'code' => 'gis.view', 'module' => 'gis'],
            ['name' => 'Manage Towers & Splitters', 'code' => 'gis.manage_towers', 'module' => 'gis'],
            ['name' => 'Manage Service Area Polygons', 'code' => 'gis.manage_service_areas', 'module' => 'gis'],
            ['name' => 'Import GIS Coordinates', 'code' => 'gis.import', 'module' => 'gis'],
            ['name' => 'Export GIS Data', 'code' => 'gis.export', 'module' => 'gis'],

            // Online Applications & Serviceability
            ['name' => 'View Service Applications', 'code' => 'applications.view', 'module' => 'applications'],
            ['name' => 'Run Technical Serviceability Checks', 'code' => 'serviceability.check', 'module' => 'serviceability'],
            ['name' => 'Override Serviceability Results', 'code' => 'serviceability.override', 'module' => 'serviceability'],

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
            'NOC' => ['name' => 'NOC Engineer', 'description' => 'Network infrastructure and GIS mapping'],
            'TECHNICAL_SUPERVISOR' => ['name' => 'Technical Supervisor', 'description' => 'Field crew dispatch and GIS planning'],
            'TECHNICAL' => ['name' => 'Field Technician', 'description' => 'Field surveys and location capture'],
            'CUSTOMER_SERVICE' => ['name' => 'Customer Service Rep', 'description' => 'CRM and support ticket management'],
            'SALES' => ['name' => 'Sales Agent', 'description' => 'Prospect registration and serviceability checking'],
        ];

        foreach ($roles as $code => $data) {
            $roleModel = Role::updateOrCreate(['code' => $code], [
                'name' => $data['name'],
                'description' => $data['description'],
            ]);

            if ($code === 'SUPER_ADMIN' || $code === 'ADMIN' || $code === 'MANAGER' || $code === 'NOC' || $code === 'TECHNICAL_SUPERVISOR') {
                $roleModel->permissions()->sync(array_column($permissionModels, 'id'));
            } else {
                $roleModel->permissions()->sync([
                    $permissionModels['gis.view']->id,
                    $permissionModels['serviceability.check']->id,
                    $permissionModels['customers.view']->id,
                    $permissionModels['applications.view']->id,
                ]);
            }
        }
    }
}
