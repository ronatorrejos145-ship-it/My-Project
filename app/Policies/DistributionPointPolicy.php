<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DistributionPoint;

class DistributionPointPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('gis.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('NOC');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('gis.manage_towers') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('NOC');
    }

    public function update(User $user, DistributionPoint $dp): bool
    {
        return $user->hasPermission('gis.manage_towers') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('NOC');
    }

    public function delete(User $user, DistributionPoint $dp): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}
