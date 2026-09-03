<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;

class CustomerPortalPolicy
{
    public function viewCustomerData(User $user, Customer $customer): bool
    {
        return $user->id === $customer->user_id || $user->hasPermission('manage-customers') || $user->hasRole('SUPER_ADMIN');
    }

    public function viewInvoice(User $user, Invoice $invoice): bool
    {
        $customer = Customer::where('user_id', $user->id)->first();
        return ($customer && $invoice->customer_id === $customer->id) || $user->hasPermission('view-finance') || $user->hasRole('SUPER_ADMIN');
    }

    public function viewPayment(User $user, Payment $payment): bool
    {
        $customer = Customer::where('user_id', $user->id)->first();
        return ($customer && $payment->customer_id === $customer->id) || $user->hasPermission('view-finance') || $user->hasRole('SUPER_ADMIN');
    }
}
