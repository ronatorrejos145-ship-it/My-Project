<?php

namespace App\Policies;

use App\Models\ReconnectionRequest;
use App\Models\User;

class ReconnectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-reconnections') || $user->hasPermissionTo('manage-collections') || $user->hasPermissionTo('view-finance');
    }

    public function request(User $user): bool
    {
        return $user->hasPermissionTo('request-reconnections') || $user->hasPermissionTo('manage-collections');
    }

    public function approve(User $user): bool
    {
        return $user->hasPermissionTo('approve-reconnections') || $user->hasRole('SUPER_ADMIN');
    }
}
