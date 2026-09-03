<?php

namespace App\Policies;

use App\Models\InstallationWorkOrder;
use App\Models\User;

class InstallationWorkOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('installations.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL') || $user->hasRole('TECHNICAL_SUPERVISOR') || $user->hasRole('TECHNICIAN') || $user->hasRole('NOC');
    }

    public function view(User $user, InstallationWorkOrder $installation): bool
    {
        if ($user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL_SUPERVISOR')) {
            return true;
        }

        if ($user->employee && $installation->assigned_technician_id === $user->employee->id) {
            return true;
        }

        if ($user->customer && $installation->customer_id === $user->customer->id) {
            return true;
        }

        return $user->hasPermission('installations.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('installations.create') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL_SUPERVISOR') || $user->hasRole('CUSTOMER_SERVICE');
    }

    public function assign(User $user, InstallationWorkOrder $installation): bool
    {
        return $user->hasPermission('installations.assign') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL_SUPERVISOR');
    }

    public function schedule(User $user, InstallationWorkOrder $installation): bool
    {
        return $user->hasPermission('installations.schedule') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL_SUPERVISOR');
    }

    public function arrive(User $user, InstallationWorkOrder $installation): bool
    {
        return $user->hasPermission('installations.arrive') || $user->hasRole('SUPER_ADMIN') || ($user->employee && $installation->assigned_technician_id === $user->employee->id);
    }

    public function review(User $user, InstallationWorkOrder $installation): bool
    {
        return $user->hasPermission('installations.supervisor.approve') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('TECHNICAL_SUPERVISOR');
    }

    public function complete(User $user, InstallationWorkOrder $installation): bool
    {
        return $user->hasPermission('installations.complete') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL_SUPERVISOR');
    }
}
