<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('leads.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('SALES') || $user->hasRole('CUSTOMER_SERVICE');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->hasPermission('leads.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('SALES') || $user->hasRole('CUSTOMER_SERVICE');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('leads.create') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('SALES') || $user->hasRole('CUSTOMER_SERVICE');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->hasPermission('leads.update') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('SALES');
    }

    public function convert(User $user, Lead $lead): bool
    {
        return $user->hasPermission('leads.convert') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('SALES');
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}
