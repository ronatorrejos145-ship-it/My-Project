<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Company;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-companies') || $user->hasRole('SUPER_ADMIN');
    }

    public function view(User $user, Company $company): bool
    {
        return $user->hasPermission('view-companies') || $user->hasRole('SUPER_ADMIN');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-companies') || $user->hasRole('SUPER_ADMIN');
    }

    public function update(User $user, Company $company): bool
    {
        return $user->hasPermission('manage-companies') || $user->hasRole('SUPER_ADMIN');
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}
