<?php

namespace App\Policies;

use App\Models\ReconnectionRequest;
use App\Models\User;

class ReconnectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-reconnections') || $user->hasPermission('manage-collections') || $user->hasPermission('view-finance') || $user->hasRole('SUPER_ADMIN');
    }

    public function request(User $user): bool
    {
        return $user->hasPermission('request-reconnections') || $user->hasPermission('manage-collections') || $user->hasRole('SUPER_ADMIN');
    }

    public function approve(User $user): bool
    {
        return $user->hasPermission('approve-reconnections') || $user->hasRole('SUPER_ADMIN');
    }
}
