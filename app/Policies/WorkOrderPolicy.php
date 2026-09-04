<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'maintenance.view') || $user->hasRole('SUPER_ADMIN');
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        if ($user->hasRole('SUPER_ADMIN') || $this->hasPermission($user, 'maintenance.view_all')) {
            return true;
        }

        // Technicians can view assigned work orders
        if ($workOrder->assigned_technician_id === $user->id) {
            return true;
        }

        // Customer user isolation
        if ($user->customer_id && $workOrder->customer_id === $user->customer_id) {
            return true;
        }

        return $this->hasPermission($user, 'maintenance.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'maintenance.create') || $user->hasRole('SUPER_ADMIN');
    }

    public function update(User $user, WorkOrder $workOrder): bool
    {
        if ($user->hasRole('SUPER_ADMIN')) {
            return true;
        }

        if ($workOrder->assigned_technician_id === $user->id) {
            return true;
        }

        return $this->hasPermission($user, 'maintenance.update');
    }

    public function dispatch(User $user): bool
    {
        return $this->hasPermission($user, 'maintenance.dispatch') || $user->hasRole('SUPER_ADMIN');
    }

    public function execute(User $user, WorkOrder $workOrder): bool
    {
        if ($user->hasRole('SUPER_ADMIN')) {
            return true;
        }

        return $workOrder->assigned_technician_id === $user->id || $this->hasPermission($user, 'maintenance.execute');
    }

    public function approve(User $user): bool
    {
        return $this->hasPermission($user, 'maintenance.approve') || $user->hasRole('SUPER_ADMIN');
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission($permission);
        }
        return true;
    }
}
