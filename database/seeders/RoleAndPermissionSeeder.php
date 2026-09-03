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
            ['name' => 'Update Users', 'code' => 'users.update', 'module' => 'users'],
            ['name' => 'Delete Users', 'code' => 'users.delete', 'module' => 'users'],

            // Customer Management
            ['name' => 'View Customers', 'code' => 'customers.view', 'module' => 'customers'],
            ['name' => 'Create Customers', 'code' => 'customers.create', 'module' => 'customers'],
            ['name' => 'Update Customers', 'code' => 'customers.update', 'module' => 'customers'],
            ['name' => 'Delete Customers', 'code' => 'customers.delete', 'module' => 'customers'],
            ['name' => 'Assign Customer Accounts', 'code' => 'customers.assign', 'module' => 'customers'],
            ['name' => 'Change Customer Status', 'code' => 'customers.change_status', 'module' => 'customers'],
            ['name' => 'View Customer Documents', 'code' => 'customers.view_documents', 'module' => 'customers'],
            ['name' => 'Upload Customer Documents', 'code' => 'customers.upload_documents', 'module' => 'customers'],
            ['name' => 'Verify Customer Documents', 'code' => 'customers.verify_documents', 'module' => 'customers'],
            ['name' => 'Export Customer Master Data', 'code' => 'customers.export', 'module' => 'customers'],

            // Online Applications & Serviceability
            ['name' => 'View Service Applications', 'code' => 'applications.view', 'module' => 'applications'],
            ['name' => 'Create Service Applications', 'code' => 'applications.create', 'module' => 'applications'],
            ['name' => 'Update Service Applications', 'code' => 'applications.update', 'module' => 'applications'],
            ['name' => 'Review Service Applications', 'code' => 'applications.review', 'module' => 'applications'],
            ['name' => 'Approve Service Applications', 'code' => 'applications.approve', 'module' => 'applications'],
            ['name' => 'Reject Service Applications', 'code' => 'applications.reject', 'module' => 'applications'],
            ['name' => 'Cancel Service Applications', 'code' => 'applications.cancel', 'module' => 'applications'],
            ['name' => 'Run Technical Serviceability Checks', 'code' => 'serviceability.check', 'module' => 'serviceability'],
            ['name' => 'Override Serviceability Results', 'code' => 'serviceability.override', 'module' => 'serviceability'],

            // Product Catalog
            ['name' => 'View Service Packages', 'code' => 'packages.view', 'module' => 'packages'],
            ['name' => 'Create Service Packages', 'code' => 'packages.create', 'module' => 'packages'],
            ['name' => 'Update Service Packages', 'code' => 'packages.update', 'module' => 'packages'],
            ['name' => 'Approve Service Packages', 'code' => 'packages.approve', 'module' => 'packages'],
            ['name' => 'Create Package Versions', 'code' => 'packages.versions.create', 'module' => 'packages'],

            // Lead Management
            ['name' => 'View CRM Leads', 'code' => 'leads.view', 'module' => 'leads'],
            ['name' => 'Create CRM Leads', 'code' => 'leads.create', 'module' => 'leads'],
            ['name' => 'Update CRM Leads', 'code' => 'leads.update', 'module' => 'leads'],
            ['name' => 'Assign CRM Leads', 'code' => 'leads.assign', 'module' => 'leads'],
            ['name' => 'Convert Lead to Customer', 'code' => 'leads.convert', 'module' => 'leads'],

            // Employee Management
            ['name' => 'View Employees', 'code' => 'employees.view', 'module' => 'employees'],
            ['name' => 'Create Employees', 'code' => 'employees.create', 'module' => 'employees'],

            // Technical & Field
            ['name' => 'View Technical Work Orders', 'code' => 'technical.view', 'module' => 'technical'],
            ['name' => 'Create Technical Work Orders', 'code' => 'technical.create', 'module' => 'technical'],

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
            'CUSTOMER_SERVICE' => ['name' => 'Customer Service Rep', 'description' => 'CRM and support ticket management'],
            'SALES' => ['name' => 'Sales Agent', 'description' => 'Prospect registration and subscription sales'],
            'TECHNICAL_SUPERVISOR' => ['name' => 'Technical Supervisor', 'description' => 'Field crew dispatch and work order approval'],
            'TECHNICAL' => ['name' => 'Field Technician', 'description' => 'Technical surveys, installations and repairs'],
        ];

        foreach ($roles as $code => $data) {
            $roleModel = Role::updateOrCreate(['code' => $code], [
                'name' => $data['name'],
                'description' => $data['description'],
            ]);

            if ($code === 'SUPER_ADMIN' || $code === 'ADMIN' || $code === 'MANAGER') {
                $roleModel->permissions()->sync(array_column($permissionModels, 'id'));
            } elseif ($code === 'CUSTOMER_SERVICE' || $code === 'SALES') {
                $roleModel->permissions()->sync([
                    $permissionModels['applications.view']->id,
                    $permissionModels['applications.create']->id,
                    $permissionModels['applications.review']->id,
                    $permissionModels['serviceability.check']->id,
                    $permissionModels['customers.view']->id,
                    $permissionModels['customers.create']->id,
                ]);
            } elseif ($code === 'TECHNICAL_SUPERVISOR') {
                $roleModel->permissions()->sync([
                    $permissionModels['applications.view']->id,
                    $permissionModels['applications.review']->id,
                    $permissionModels['serviceability.check']->id,
                    $permissionModels['serviceability.override']->id,
                ]);
            }
        }
    }
}
