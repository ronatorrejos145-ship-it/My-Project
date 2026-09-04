<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('procurement.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE') || $user->hasRole('FINANCE');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('procurement.create') || $user->hasPermission('procurement.purchase_order') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE');
    }
}
