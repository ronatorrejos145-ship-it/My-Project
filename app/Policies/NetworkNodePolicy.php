<?php

namespace App\Policies;

use App\Models\User;
use App\Models\NetworkNode;

class NetworkNodePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-network-nodes') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL') || $user->hasRole('NOC');
    }

    public function view(User $user, NetworkNode $networkNode): bool
    {
        return $user->hasPermission('view-network-nodes') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL') || $user->hasRole('NOC');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-network-nodes') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('NOC');
    }

    public function update(User $user, NetworkNode $networkNode): bool
    {
        return $user->hasPermission('manage-network-nodes') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('NOC');
    }

    public function delete(User $user, NetworkNode $networkNode): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}
