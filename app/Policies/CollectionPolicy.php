<?php

namespace App\Policies;

use App\Models\CollectionAccount;
use App\Models\User;

class CollectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-collections') || $user->hasPermission('manage-collections') || $user->hasPermission('view-finance') || $user->hasRole('SUPER_ADMIN');
    }

    public function createAction(User $user): bool
    {
        return $user->hasPermission('manage-collections') || $user->hasPermission('execute-collections') || $user->hasRole('SUPER_ADMIN');
    }

    public function manageArrangements(User $user): bool
    {
        return $user->hasPermission('manage-collections') || $user->hasPermission('approve-arrangements') || $user->hasRole('SUPER_ADMIN');
    }
}
