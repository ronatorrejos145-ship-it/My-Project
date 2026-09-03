<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('assets.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE') || $user->hasRole('TECHNICAL') || $user->hasRole('TECHNICAL_SUPERVISOR') || $user->hasRole('TECHNICIAN');
    }

    public function view(User $user, Asset $asset): bool
    {
        return $user->hasPermission('assets.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE') || $user->hasRole('TECHNICAL') || $user->hasRole('TECHNICIAN');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('assets.create') || $user->hasPermission('assets.receive') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE');
    }

    public function update(User $user, Asset $asset): bool
    {
        return $user->hasPermission('assets.update') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE');
    }

    public function transfer(User $user, Asset $asset): bool
    {
        return $user->hasPermission('assets.transfer') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE');
    }

    public function verify(User $user, Asset $asset): bool
    {
        return $user->hasPermission('assets.verify') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE') || $user->hasRole('TECHNICIAN');
    }

    public function retire(User $user, Asset $asset): bool
    {
        return $user->hasPermission('assets.retire') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN');
    }
}
