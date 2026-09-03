<?php

namespace App\Policies;

use App\Models\Discount;
use App\Models\User;

class DiscountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-finance') || $user->hasPermissionTo('manage-discounts');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage-discounts') || $user->hasRole('SUPER_ADMIN');
    }

    public function update(User $user, Discount $discount): bool
    {
        return $user->hasPermissionTo('manage-discounts') || $user->hasRole('SUPER_ADMIN');
    }
}
