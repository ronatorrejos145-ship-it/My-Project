<?php

namespace App\Services;

use App\Models\AccountBalanceSnapshot;
use App\Models\Customer;
use App\Models\LedgerTransaction;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    public function getCustomerBalance(int $customerId, ?int $serviceAccountId = null): float
    {
        $query = LedgerTransaction::where('customer_id', $customerId)
            ->where('status', 'POSTED');

        if ($serviceAccountId) {
            $query->where('service_account_id', $serviceAccountId);
        }

        $totalDebits = (float) $query->sum('debit_amount');
        $totalCredits = (float) $query->sum('credit_amount');

        return round($totalDebits - $totalCredits, 2);
    }

    public function rebuildSnapshot(int $customerId, ?int $serviceAccountId = null): AccountBalanceSnapshot
    {
        return DB::transaction(function () use ($customerId, $serviceAccountId) {
            $query = LedgerTransaction::where('customer_id', $customerId)
                ->where('status', 'POSTED');

            if ($serviceAccountId) {
                $query->where('service_account_id', $serviceAccountId);
            }

            $totalDebits = (float) $query->sum('debit_amount');
            $totalCredits = (float) $query->sum('credit_amount');
            $currentBalance = round($totalDebits - $totalCredits, 2);
            $lastTx = $query->latest('id')->first();

            $snapshot = AccountBalanceSnapshot::updateOrCreate(
                [
                    'customer_id' => $customerId,
                    'service_account_id' => $serviceAccountId,
                ],
                [
                    'total_debits' => $totalDebits,
                    'total_credits' => $totalCredits,
                    'current_balance' => $currentBalance,
                    'last_transaction_id' => $lastTx?->id,
                    'last_calculated_at' => now(),
                ]
            );

            // Sync balance to customer model
            if (!$serviceAccountId) {
                Customer::where('id', $customerId)->update(['current_balance' => $currentBalance]);
            }

            return $snapshot;
        });
    }
}
