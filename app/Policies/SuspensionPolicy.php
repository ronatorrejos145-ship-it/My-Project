<?php

namespace App\Policies;

use App\Models\SuspensionRequest;
use App\Models\User;

class SuspensionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-suspensions') || $user->hasPermissionTo('manage-collections') || $user->hasPermissionTo('view-finance');
    }

    public function request(User $user): bool
    {
        return $user->hasPermissionTo('request-suspensions') || $user->hasPermissionTo('manage-collections');
    }

    public function approve(User $user): bool
    {
        return $user->hasPermissionTo('approve-suspensions') || $user->hasRole('SUPER_ADMIN');
    }
}
