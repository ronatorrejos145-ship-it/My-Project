<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('customers.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('CUSTOMER_SERVICE') || $user->hasRole('SALES') || $user->hasRole('TECHNICIAN');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->hasPermission('customers.view') || $user->id === $customer->user_id || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('CUSTOMER_SERVICE') || $user->hasRole('SALES') || $user->hasRole('TECHNICIAN');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('customers.create') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('CUSTOMER_SERVICE') || $user->hasRole('SALES');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->hasPermission('customers.update') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('CUSTOMER_SERVICE');
    }

    public function changeStatus(User $user, Customer $customer): bool
    {
        return $user->hasPermission('customers.change_status') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('CUSTOMER_SERVICE');
    }

    public function assign(User $user, Customer $customer): bool
    {
        return $user->hasPermission('customers.assign') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('customers.export') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('MANAGER');
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->hasPermission('customers.delete') || $user->hasRole('SUPER_ADMIN');
    }
}
