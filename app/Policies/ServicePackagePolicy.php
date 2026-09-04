<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ServicePackage;

class ServicePackagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('packages.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('SALES') || $user->hasRole('CUSTOMER_SERVICE');
    }

    public function view(User $user, ServicePackage $package): bool
    {
        return $user->hasPermission('packages.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('SALES') || $user->hasRole('CUSTOMER_SERVICE');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('packages.create') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN');
    }

    public function update(User $user, ServicePackage $package): bool
    {
        return $user->hasPermission('packages.update') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN');
    }

    public function approve(User $user, ServicePackage $package): bool
    {
        return $user->hasPermission('packages.approve') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('MANAGER');
    }

    public function createVersion(User $user, ServicePackage $package): bool
    {
        return $user->hasPermission('packages.versions.create') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN');
    }

    public function delete(User $user, ServicePackage $package): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}
