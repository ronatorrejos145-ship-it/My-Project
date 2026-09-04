<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-tickets') || $user->hasPermission('manage-support') || $user->hasRole('SUPER_ADMIN');
    }

    public function view(User $user, Ticket $ticket): bool
    {
        $customer = Customer::where('user_id', $user->id)->first();
        if ($customer && $ticket->customer_id === $customer->id) {
            return true;
        }

        return $user->hasPermission('view-tickets') || $user->hasPermission('manage-support') || $user->hasRole('SUPER_ADMIN');
    }

    public function create(User $user): bool
    {
        return true; // Any authenticated user or customer can create support tickets
    }

    public function reply(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }

    public function updateStatus(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('manage-support') || $user->hasPermission('resolve-tickets') || $user->hasRole('SUPER_ADMIN');
    }
}
