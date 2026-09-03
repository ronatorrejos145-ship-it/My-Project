<?php

namespace App\Services;

use App\Models\CollectionAccount;
use App\Models\Credit;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ServiceAccount;
use App\Models\Subscription;
use App\Models\User;

class CustomerPortalService
{
    public function getDashboardData(User $user, ?int $activeServiceAccountId = null): array
    {
        $customer = Customer::where('user_id', $user->id)->first();
        if (!$customer) {
            return [
                'has_customer' => false,
                'customer' => null,
                'service_accounts' => collect([]),
            ];
        }

        $serviceAccounts = ServiceAccount::where('customer_id', $customer->id)->with('subscriptions.package')->get();
        $selectedServiceAccount = $activeServiceAccountId
            ? $serviceAccounts->firstWhere('id', $activeServiceAccountId)
            : $serviceAccounts->first();

        $activeSubscription = $selectedServiceAccount
            ? $selectedServiceAccount->subscriptions()->where('status', 'ACTIVE')->first()
            : null;

        $invoices = Invoice::where('customer_id', $customer->id)
            ->orderBy('due_date', 'desc')
            ->limit(10)
            ->get();

        $recentPayments = Payment::where('customer_id', $customer->id)
            ->orderBy('payment_date', 'desc')
            ->limit(5)
            ->get();

        $availableCredits = Credit::where('customer_id', $customer->id)
            ->where('status', 'AVAILABLE')
            ->sum('remaining_amount');

        $collectionAccount = CollectionAccount::where('customer_id', $customer->id)->first();

        return [
            'has_customer' => true,
            'customer' => $customer,
            'service_accounts' => $serviceAccounts,
            'active_service_account' => $selectedServiceAccount,
            'active_subscription' => $activeSubscription,
            'current_balance' => round((float)$customer->current_balance, 2),
            'overdue_balance' => round((float)($collectionAccount?->overdue_amount ?? 0.00), 2),
            'available_credits' => round((float)$availableCredits, 2),
            'invoices' => $invoices,
            'recent_payments' => $recentPayments,
            'delinquency_status' => $collectionAccount?->delinquency_status ?? 'CURRENT',
        ];
    }
}
