<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Promotion;

class PromotionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('promotions.manage') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('SALES');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('promotions.manage') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN');
    }

    public function update(User $user, Promotion $promotion): bool
    {
        return $user->hasPermission('promotions.manage') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN');
    }

    public function delete(User $user, Promotion $promotion): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}
