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

            // Lead Management
            ['name' => 'View CRM Leads', 'code' => 'leads.view', 'module' => 'leads'],
            ['name' => 'Create CRM Leads', 'code' => 'leads.create', 'module' => 'leads'],
            ['name' => 'Update CRM Leads', 'code' => 'leads.update', 'module' => 'leads'],
            ['name' => 'Assign CRM Leads', 'code' => 'leads.assign', 'module' => 'leads'],
            ['name' => 'Convert Lead to Customer', 'code' => 'leads.convert', 'module' => 'leads'],

            // Employee Management
            ['name' => 'View Employees', 'code' => 'employees.view', 'module' => 'employees'],
            ['name' => 'Create Employees', 'code' => 'employees.create', 'module' => 'employees'],
            ['name' => 'Update Employees', 'code' => 'employees.update', 'module' => 'employees'],
            ['name' => 'Delete Employees', 'code' => 'employees.delete', 'module' => 'employees'],

            // Billing & Ledger
            ['name' => 'View Billing', 'code' => 'billing.view', 'module' => 'billing'],
            ['name' => 'Manage Billing', 'code' => 'billing.create', 'module' => 'billing'],
            ['name' => 'View Ledger', 'code' => 'ledger.view', 'module' => 'billing'],

            // Technical & Field
            ['name' => 'View Technical Work Orders', 'code' => 'technical.view', 'module' => 'technical'],
            ['name' => 'Create Technical Work Orders', 'code' => 'technical.create', 'module' => 'technical'],

            // Network NOC
            ['name' => 'View Network NOC', 'code' => 'network.view', 'module' => 'noc'],

            // Warehouse & Tools
            ['name' => 'View Inventory', 'code' => 'inventory.view', 'module' => 'inventory'],

            // Customer Support
            ['name' => 'View Support Tickets', 'code' => 'tickets.view', 'module' => 'support'],
            ['name' => 'Create Support Tickets', 'code' => 'tickets.create', 'module' => 'support'],

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
            'FINANCE' => ['name' => 'Finance & Accounting', 'description' => 'Ledger, invoices and financial operations'],
            'CASHIER' => ['name' => 'Cashier', 'description' => 'Counter payments and payment recording'],
            'CUSTOMER_SERVICE' => ['name' => 'Customer Service Rep', 'description' => 'CRM and support ticket management'],
            'SALES' => ['name' => 'Sales Agent', 'description' => 'Prospect registration and subscription sales'],
            'TECHNICIAN' => ['name' => 'Field Technician', 'description' => 'Technical surveys, installations and repairs'],
            'TECHNICAL_SUPERVISOR' => ['name' => 'Technical Supervisor', 'description' => 'Field crew dispatch and work order approval'],
            'NOC' => ['name' => 'NOC Engineer', 'description' => 'Network infrastructure and monitoring'],
            'WAREHOUSE' => ['name' => 'Warehouse Keeper', 'description' => 'Stock, equipment and tool management'],
            'HR' => ['name' => 'Human Resources', 'description' => 'Employee records'],
            'EMPLOYEE' => ['name' => 'Staff Employee', 'description' => 'Standard internal staff'],
            'CUSTOMER' => ['name' => 'Subscriber / Customer', 'description' => 'Customer self-service portal'],
        ];

        foreach ($roles as $code => $data) {
            $roleModel = Role::updateOrCreate(['code' => $code], [
                'name' => $data['name'],
                'description' => $data['description'],
            ]);

            if ($code === 'SUPER_ADMIN' || $code === 'ADMIN') {
                $roleModel->permissions()->sync(array_column($permissionModels, 'id'));
            } elseif ($code === 'CUSTOMER_SERVICE') {
                $roleModel->permissions()->sync([
                    $permissionModels['customers.view']->id,
                    $permissionModels['customers.create']->id,
                    $permissionModels['customers.update']->id,
                    $permissionModels['customers.view_documents']->id,
                    $permissionModels['customers.upload_documents']->id,
                    $permissionModels['customers.verify_documents']->id,
                    $permissionModels['leads.view']->id,
                    $permissionModels['tickets.view']->id,
                    $permissionModels['tickets.create']->id,
                ]);
            } elseif ($code === 'SALES') {
                $roleModel->permissions()->sync([
                    $permissionModels['customers.view']->id,
                    $permissionModels['customers.create']->id,
                    $permissionModels['leads.view']->id,
                    $permissionModels['leads.create']->id,
                    $permissionModels['leads.update']->id,
                    $permissionModels['leads.convert']->id,
                ]);
            } elseif ($code === 'TECHNICIAN') {
                $roleModel->permissions()->sync([
                    $permissionModels['customers.view']->id,
                    $permissionModels['technical.view']->id,
                    $permissionModels['technical.create']->id,
                ]);
            }
        }
    }
}
