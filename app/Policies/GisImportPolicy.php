<?php

namespace App\Policies;

use App\Models\User;
use App\Models\GisImport;

class GisImportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('gis.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('NOC') || $user->hasRole('TECHNICAL_SUPERVISOR') || $user->hasRole('TECHNICAL') || $user->hasRole('CUSTOMER_SERVICE') || $user->hasRole('SALES');
    }

    public function view(User $user, ?GisImport $import = null): bool
    {
        return $user->hasPermission('gis.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('NOC') || $user->hasRole('TECHNICAL_SUPERVISOR') || $user->hasRole('TECHNICAL') || $user->hasRole('CUSTOMER_SERVICE') || $user->hasRole('SALES');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('gis.import') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('NOC');
    }
}
