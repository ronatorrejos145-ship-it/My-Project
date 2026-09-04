<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Account;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-finance') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('FINANCE');
    }

    public function view(User $user, Account $account): bool
    {
        return $user->hasPermission('view-finance') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('FINANCE');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-finance') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('FINANCE');
    }

    public function update(User $user, Account $account): bool
    {
        return $user->hasPermission('manage-finance') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('FINANCE');
    }

    public function delete(User $user, Account $account): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}
