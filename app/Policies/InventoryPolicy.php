<?php

namespace App\Policies;

use App\Models\StockBalance;
use App\Models\User;

class InventoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('inventory.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE') || $user->hasRole('TECHNICAL') || $user->hasRole('TECHNICAL_SUPERVISOR') || $user->hasRole('TECHNICIAN');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('inventory.create') || $user->hasPermission('inventory.adjust') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE');
    }

    public function update(User $user, StockBalance $balance): bool
    {
        return $user->hasPermission('inventory.update') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE');
    }
}
