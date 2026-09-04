<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;

class WarehousePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-warehouses') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE');
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermission('view-warehouses') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-warehouses') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('WAREHOUSE');
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermission('manage-warehouses') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('WAREHOUSE');
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}
