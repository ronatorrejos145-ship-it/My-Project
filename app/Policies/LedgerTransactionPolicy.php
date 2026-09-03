<?php

namespace App\Policies;

use App\Models\LedgerTransaction;
use App\Models\User;

class LedgerTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('ledger.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('FINANCE');
    }

    public function view(User $user, LedgerTransaction $tx): bool
    {
        return $user->hasPermission('ledger.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('FINANCE') || ($user->customer && $tx->customer_id === $user->customer->id);
    }
}
