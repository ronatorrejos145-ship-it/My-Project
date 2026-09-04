<?php

namespace App\Policies;

use App\Models\GoodsReceipt;
use App\Models\User;

class GoodsReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('procurement.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('procurement.receive') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE');
    }
}
