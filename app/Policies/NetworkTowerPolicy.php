<?php

namespace App\Policies;

use App\Models\User;
use App\Models\NetworkTower;

class NetworkTowerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('gis.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('NOC');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('gis.manage_towers') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('NOC');
    }

    public function update(User $user, NetworkTower $tower): bool
    {
        return $user->hasPermission('gis.manage_towers') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('NOC');
    }

    public function delete(User $user, NetworkTower $tower): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}
