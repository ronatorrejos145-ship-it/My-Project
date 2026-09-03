<?php

namespace App\Policies;

use App\Models\RefundRequest;
use App\Models\User;

class RefundPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-finance') || $user->hasPermissionTo('manage-refunds');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-refunds') || $user->hasPermissionTo('manage-finance');
    }

    public function approve(User $user, RefundRequest $request): bool
    {
        return $user->hasPermissionTo('approve-refunds') || $user->hasRole('SUPER_ADMIN');
    }

    public function reverse(User $user, RefundRequest $request): bool
    {
        return $user->hasPermissionTo('reverse-refunds') || $user->hasRole('SUPER_ADMIN');
    }
}
