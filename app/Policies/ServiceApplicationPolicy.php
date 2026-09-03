<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ServiceApplication;

class ServiceApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('applications.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('CUSTOMER_SERVICE') || $user->hasRole('SALES') || $user->hasRole('TECHNICAL_SUPERVISOR');
    }

    public function view(User $user, ServiceApplication $application): bool
    {
        return $user->hasPermission('applications.view') || ($application->customer && $user->id === $application->customer->user_id) || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('CUSTOMER_SERVICE') || $user->hasRole('SALES') || $user->hasRole('TECHNICAL_SUPERVISOR');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('applications.create') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('CUSTOMER_SERVICE') || $user->hasRole('SALES');
    }

    public function review(User $user, ServiceApplication $application): bool
    {
        return $user->hasPermission('applications.review') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('CUSTOMER_SERVICE') || $user->hasRole('TECHNICAL_SUPERVISOR');
    }

    public function approve(User $user, ServiceApplication $application): bool
    {
        return $user->hasPermission('applications.approve') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('MANAGER') || $user->hasRole('TECHNICAL_SUPERVISOR');
    }

    public function reject(User $user, ServiceApplication $application): bool
    {
        return $user->hasPermission('applications.reject') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('MANAGER');
    }
}
