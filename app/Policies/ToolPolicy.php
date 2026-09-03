<?php

namespace App\Policies;

use App\Models\Tool;
use App\Models\User;

class ToolPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('tools.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE') || $user->hasRole('TECHNICAL') || $user->hasRole('TECHNICIAN');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('tools.create') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE');
    }

    public function update(User $user, Tool $tool): bool
    {
        return $user->hasPermission('tools.update') || $user->hasPermission('tools.issue') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('WAREHOUSE');
    }
}
