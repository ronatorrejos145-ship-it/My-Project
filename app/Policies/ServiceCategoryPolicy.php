<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ServiceCategory;

class ServiceCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('packages.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('SALES');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('packages.create') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN');
    }

    public function update(User $user, ServiceCategory $category): bool
    {
        return $user->hasPermission('packages.update') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN');
    }

    public function delete(User $user, ServiceCategory $category): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}
