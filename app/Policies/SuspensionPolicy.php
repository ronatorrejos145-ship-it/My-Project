<?php

namespace App\Policies;

use App\Models\SuspensionRequest;
use App\Models\User;

class SuspensionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-suspensions') || $user->hasPermission('manage-collections') || $user->hasPermission('view-finance') || $user->hasRole('SUPER_ADMIN');
    }

    public function request(User $user): bool
    {
        return $user->hasPermission('request-suspensions') || $user->hasPermission('manage-collections') || $user->hasRole('SUPER_ADMIN');
    }

    public function approve(User $user): bool
    {
        return $user->hasPermission('approve-suspensions') || $user->hasRole('SUPER_ADMIN');
    }
}
