<?php

namespace App\Policies;

use App\Models\Credit;
use App\Models\User;

class CreditPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-finance') || $user->hasPermission('manage-credits') || $user->hasRole('SUPER_ADMIN');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-credits') || $user->hasPermission('create-credits') || $user->hasRole('SUPER_ADMIN');
    }

    public function apply(User $user, Credit $credit): bool
    {
        return $user->hasPermission('apply-credits') || $user->hasPermission('manage-finance') || $user->hasRole('SUPER_ADMIN');
    }
}
