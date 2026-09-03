<?php

namespace App\Policies;

use App\Models\Credit;
use App\Models\User;

class CreditPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-finance') || $user->hasPermissionTo('manage-credits');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage-credits') || $user->hasPermissionTo('create-credits');
    }

    public function apply(User $user, Credit $credit): bool
    {
        return $user->hasPermissionTo('apply-credits') || $user->hasPermissionTo('manage-finance');
    }
}
