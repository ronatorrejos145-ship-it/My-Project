<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ServiceArea;

class ServiceAreaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-service-areas') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL');
    }

    public function view(User $user, ServiceArea $serviceArea): bool
    {
        return $user->hasPermission('view-service-areas') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-service-areas') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN');
    }

    public function update(User $user, ServiceArea $serviceArea): bool
    {
        return $user->hasPermission('manage-service-areas') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN');
    }

    public function delete(User $user, ServiceArea $serviceArea): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}
