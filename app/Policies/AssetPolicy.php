<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Asset;

class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-assets') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE') || $user->hasRole('TECHNICAL');
    }

    public function view(User $user, Asset $asset): bool
    {
        return $user->hasPermission('view-assets') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE') || $user->hasRole('TECHNICAL');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-assets') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('WAREHOUSE');
    }

    public function update(User $user, Asset $asset): bool
    {
        return $user->hasPermission('manage-assets') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('WAREHOUSE');
    }

    public function delete(User $user, Asset $asset): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}
