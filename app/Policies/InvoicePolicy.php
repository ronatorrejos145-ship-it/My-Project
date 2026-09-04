<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('invoices.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('FINANCE') || $user->hasRole('CASHIER') || $user->hasRole('CUSTOMER_SERVICE');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('invoices.view') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('FINANCE') || ($user->customer && $invoice->customer_id === $user->customer->id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('invoices.create') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('FINANCE');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('invoices.finalize') || $user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN') || $user->hasRole('FINANCE');
    }
}
