<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LedgerTransaction;
use App\Models\ServiceAccount;

class StatementService
{
    public function __construct(protected BalanceService $balanceService) {}

    public function generateStatement(
        Customer $customer,
        ?ServiceAccount $serviceAccount = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = LedgerTransaction::where('customer_id', $customer->id)
            ->where('status', 'POSTED')
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');

        if ($serviceAccount) {
            $query->where('service_account_id', $serviceAccount->id);
        }

        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        $transactions = $query->get();

        $runningBalance = 0.00;
        $statementLines = [];

        foreach ($transactions as $tx) {
            $debit = (float) $tx->debit_amount;
            $credit = (float) $tx->credit_amount;
            $runningBalance += ($debit - $credit);

            $statementLines[] = [
                'transaction_number' => $tx->transaction_number,
                'transaction_date' => $tx->transaction_date->format('Y-m-d'),
                'type' => $tx->transaction_type,
                'description' => $tx->description,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => round($runningBalance, 2),
            ];
        }

        return [
            'customer_number' => $customer->customer_number,
            'customer_name' => $customer->full_name,
            'service_account_number' => $serviceAccount?->account_number,
            'period_start' => $startDate ?? 'Beginning',
            'period_end' => $endDate ?? date('Y-m-d'),
            'opening_balance' => 0.00,
            'closing_balance' => round($runningBalance, 2),
            'lines' => $statementLines,
        ];
    }
}
