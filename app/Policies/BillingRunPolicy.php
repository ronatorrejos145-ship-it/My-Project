<?php

namespace App\Policies;

use App\Models\BillingRun;
use App\Models\User;

class BillingRunPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('billing.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('FINANCE');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('billing.run') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('FINANCE');
    }
}
