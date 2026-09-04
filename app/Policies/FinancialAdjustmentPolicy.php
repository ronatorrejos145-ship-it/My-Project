<?php

namespace App\Policies;

use App\Models\FinancialAdjustment;
use App\Models\User;

class FinancialAdjustmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-finance') || $user->hasPermission('manage-adjustments') || $user->hasRole('SUPER_ADMIN');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-adjustments') || $user->hasPermission('manage-finance') || $user->hasRole('SUPER_ADMIN');
    }

    public function approve(User $user, FinancialAdjustment $adjustment): bool
    {
        return $user->hasPermission('approve-adjustments') || $user->hasRole('SUPER_ADMIN');
    }
}
