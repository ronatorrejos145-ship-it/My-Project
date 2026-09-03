<?php

namespace App\Policies;

use App\Models\ServiceAccount;
use App\Models\User;

class ServiceAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('services.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('CUSTOMER_SERVICE') || $user->hasRole('TECHNICAL');
    }

    public function view(User $user, ServiceAccount $serviceAccount): bool
    {
        return $user->hasPermission('services.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || ($user->customer && $serviceAccount->customer_id === $user->customer->id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('services.create') || $user->hasPermission('services.activate') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('CUSTOMER_SERVICE');
    }

    public function update(User $user, ServiceAccount $serviceAccount): bool
    {
        return $user->hasPermission('services.update') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('CUSTOMER_SERVICE');
    }
}
