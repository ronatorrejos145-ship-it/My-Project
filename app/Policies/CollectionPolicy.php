<?php

namespace App\Policies;

use App\Models\CollectionAccount;
use App\Models\User;

class CollectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-collections') || $user->hasPermissionTo('manage-collections') || $user->hasPermissionTo('view-finance');
    }

    public function createAction(User $user): bool
    {
        return $user->hasPermissionTo('manage-collections') || $user->hasPermissionTo('execute-collections');
    }

    public function manageArrangements(User $user): bool
    {
        return $user->hasPermissionTo('manage-collections') || $user->hasPermissionTo('approve-arrangements') || $user->hasRole('SUPER_ADMIN');
    }
}
