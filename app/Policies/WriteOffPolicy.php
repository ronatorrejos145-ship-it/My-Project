<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WriteOffRequest;

class WriteOffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-writeoffs') || $user->hasPermission('view-finance') || $user->hasRole('SUPER_ADMIN');
    }

    public function request(User $user): bool
    {
        return $user->hasPermission('request-writeoffs') || $user->hasPermission('manage-finance') || $user->hasRole('SUPER_ADMIN');
    }

    public function approve(User $user): bool
    {
        return $user->hasPermission('approve-writeoffs') || $user->hasRole('SUPER_ADMIN');
    }
}
