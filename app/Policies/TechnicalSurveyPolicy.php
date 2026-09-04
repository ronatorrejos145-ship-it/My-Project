<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TechnicalSurvey;

class TechnicalSurveyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('technical_surveys.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL_SUPERVISOR') || $user->hasRole('TECHNICAL') || $user->hasRole('CUSTOMER_SERVICE');
    }

    public function view(User $user, TechnicalSurvey $survey): bool
    {
        if ($user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL_SUPERVISOR') || $user->hasPermission('technical_surveys.view')) {
            return true;
        }

        // Technicians can view surveys assigned to them
        return $user->employee && $survey->technician_id === $user->employee->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('technical_surveys.create') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL_SUPERVISOR') || $user->hasRole('CUSTOMER_SERVICE');
    }

    public function assign(User $user, TechnicalSurvey $survey): bool
    {
        return $user->hasPermission('technical_surveys.assign') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL_SUPERVISOR');
    }

    public function submit(User $user, TechnicalSurvey $survey): bool
    {
        if ($user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL_SUPERVISOR')) {
            return true;
        }

        return $user->employee && $survey->technician_id === $user->employee->id;
    }

    public function review(User $user, TechnicalSurvey $survey): bool
    {
        return $user->hasPermission('technical_surveys.review') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('TECHNICAL_SUPERVISOR') || $user->hasRole('MANAGER');
    }

    public function delete(User $user, TechnicalSurvey $survey): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}
