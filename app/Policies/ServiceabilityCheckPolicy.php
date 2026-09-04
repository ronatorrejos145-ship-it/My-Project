<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ServiceabilityCheck;

class ServiceabilityCheckPolicy
{
    public function check(User $user): bool
    {
        return $user->hasPermission('serviceability.check') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('CUSTOMER_SERVICE') || $user->hasRole('SALES') || $user->hasRole('TECHNICAL_SUPERVISOR');
    }

    public function override(User $user, ServiceabilityCheck $check): bool
    {
        return $user->hasPermission('serviceability.override') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL_SUPERVISOR') || $user->hasRole('MANAGER');
    }
}
