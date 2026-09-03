<?php

namespace App\Policies;

use App\Models\FinancialAdjustment;
use App\Models\User;

class FinancialAdjustmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-finance') || $user->hasPermissionTo('manage-adjustments');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-adjustments') || $user->hasPermissionTo('manage-finance');
    }

    public function approve(User $user, FinancialAdjustment $adjustment): bool
    {
        return $user->hasPermissionTo('approve-adjustments') || $user->hasRole('SUPER_ADMIN');
    }
}
