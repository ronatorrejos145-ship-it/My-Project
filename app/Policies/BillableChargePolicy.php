<?php

namespace App\Policies;

use App\Models\BillableCharge;
use App\Models\User;

class BillableChargePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('billing.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('FINANCE') || $user->hasRole('CUSTOMER_SERVICE');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('billing.create') || $user->hasPermission('billing.run') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('FINANCE');
    }
}
