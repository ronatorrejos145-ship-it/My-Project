<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Branch;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-branches') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN');
    }

    public function view(User $user, Branch $branch): bool
    {
        return $user->hasPermission('view-branches') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-branches') || $user->hasRole('SUPER_ADMIN');
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->hasPermission('manage-branches') || $user->hasRole('SUPER_ADMIN');
    }

    public function delete(User $user, Branch $branch): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}
