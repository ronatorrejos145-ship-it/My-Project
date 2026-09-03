<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WriteOffRequest;

class WriteOffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-writeoffs') || $user->hasPermissionTo('view-finance');
    }

    public function request(User $user): bool
    {
        return $user->hasPermissionTo('request-writeoffs') || $user->hasPermissionTo('manage-finance');
    }

    public function approve(User $user): bool
    {
        return $user->hasPermissionTo('approve-writeoffs') || $user->hasRole('SUPER_ADMIN');
    }
}
