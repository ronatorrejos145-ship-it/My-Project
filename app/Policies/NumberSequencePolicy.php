<?php

namespace App\Policies;

use App\Models\User;
use App\Models\NumberSequence;

class NumberSequencePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-settings') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN');
    }

    public function view(User $user, NumberSequence $sequence): bool
    {
        return $user->hasPermission('view-settings') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN');
    }

    public function update(User $user, NumberSequence $sequence): bool
    {
        return $user->hasPermission('manage-settings') || $user->hasRole('SUPER_ADMIN');
    }
}
