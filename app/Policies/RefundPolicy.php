<?php

namespace App\Policies;

use App\Models\RefundRequest;
use App\Models\User;

class RefundPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-finance') || $user->hasPermission('manage-refunds') || $user->hasRole('SUPER_ADMIN');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-refunds') || $user->hasPermission('manage-finance') || $user->hasRole('SUPER_ADMIN');
    }

    public function approve(User $user, RefundRequest $request): bool
    {
        return $user->hasPermission('approve-refunds') || $user->hasRole('SUPER_ADMIN');
    }

    public function reverse(User $user, RefundRequest $request): bool
    {
        return $user->hasPermission('reverse-refunds') || $user->hasRole('SUPER_ADMIN');
    }
}
