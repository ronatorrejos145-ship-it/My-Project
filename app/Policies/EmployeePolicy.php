<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('employees.view');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->hasPermission('employees.view') || $user->id === $employee->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('employees.create');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasPermission('employees.update');
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasPermission('employees.delete');
    }
}
