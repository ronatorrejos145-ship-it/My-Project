<?php

namespace App\Policies;

use App\Models\User;

class GisPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('gis.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('NOC') || $user->hasRole('TECHNICAL_SUPERVISOR') || $user->hasRole('TECHNICAL') || $user->hasRole('CUSTOMER_SERVICE') || $user->hasRole('SALES');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('gis.import') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('NOC');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('gis.export') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('NOC') || $user->hasRole('MANAGER');
    }
}
