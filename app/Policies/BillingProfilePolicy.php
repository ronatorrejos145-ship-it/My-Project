<?php

namespace App\Policies;

use App\Models\BillingProfile;
use App\Models\User;

class BillingProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('billing.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('FINANCE');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('billing.create') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('FINANCE');
    }
}
