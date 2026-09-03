<?php

namespace App\Services;

use App\Models\CollectionAccount;
use App\Models\Customer;
use App\Models\DelinquencyHistory;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class DelinquencyEngineService
{
    public function evaluateCustomerDelinquency(Customer $customer): CollectionAccount
    {
        return DB::transaction(function () use ($customer) {
            $invoices = Invoice::where('customer_id', $customer->id)
                ->whereIn('status', ['UNPAID', 'PARTIALLY_PAID', 'OVERDUE'])
                ->orderBy('due_date', 'asc')
                ->get();

            $totalOutstanding = 0.00;
            $totalOverdue = 0.00;
            $maxDaysOverdue = 0;
            $overdueCount = 0;
            $oldestInvoiceDate = null;
            $oldestInvoiceNum = null;

            $now = now()->startOfDay();

            foreach ($invoices as $inv) {
                $paid = (float)($inv->paid_amount ?? $inv->amount_paid ?? 0.00);
                $unpaid = round((float)$inv->total_amount - $paid, 2);
                if ($unpaid <= 0) continue;

                $totalOutstanding += $unpaid;
                $dueDate = \Carbon\Carbon::parse($inv->due_date)->startOfDay();

                if ($now->gt($dueDate)) {
                    $days = $now->diffInDays($dueDate);
                    $totalOverdue += $unpaid;
                    $overdueCount++;

                    if ($days > $maxDaysOverdue) {
                        $maxDaysOverdue = $days;
                        $oldestInvoiceDate = $inv->due_date;
                        $oldestInvoiceNum = $inv->invoice_number;
                    }

                    if ($inv->status !== 'OVERDUE') {
                        $inv->update(['status' => 'OVERDUE']);
                    }
                }
            }

            // State Machine Status Determination
            $newStatus = 'CURRENT';
            if ($maxDaysOverdue > 0) {
                if ($maxDaysOverdue <= 3) {
                    $newStatus = 'GRACE_PERIOD';
                } elseif ($maxDaysOverdue <= 7) {
                    $newStatus = 'OVERDUE';
                } elseif ($maxDaysOverdue <= 14) {
                    $newStatus = 'COLLECTION_WARNING';
                } else {
                    $newStatus = 'SUSPENSION_ELIGIBLE';
                }
            }

            $collAccount = CollectionAccount::firstOrCreate(
                ['customer_id' => $customer->id],
                ['delinquency_status' => 'CURRENT']
            );

            $oldStatus = $collAccount->delinquency_status;

            // Preserve SUSPENDED or WRITTEN_OFF status if not fully paid
            if (in_array($oldStatus, ['SUSPENDED', 'WRITTEN_OFF']) && $totalOutstanding > 0) {
                $newStatus = $oldStatus;
            } elseif ($totalOutstanding <= 0) {
                $newStatus = 'CURRENT';
            }

            if ($oldStatus !== $newStatus) {
                DelinquencyHistory::create([
                    'customer_id' => $customer->id,
                    'service_account_id' => $collAccount->service_account_id,
                    'previous_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'reason' => "Automated delinquency evaluation ({$maxDaysOverdue} days overdue, ₱{$totalOverdue} overdue)",
                ]);
            }

            $riskLevel = 'LOW';
            if ($maxDaysOverdue > 30) $riskLevel = 'CRITICAL';
            elseif ($maxDaysOverdue > 14) $riskLevel = 'HIGH';
            elseif ($maxDaysOverdue > 7) $riskLevel = 'MEDIUM';

            $collAccount->update([
                'delinquency_status' => $newStatus,
                'oldest_unpaid_invoice_date' => $oldestInvoiceDate,
                'oldest_unpaid_invoice_number' => $oldestInvoiceNum,
                'total_outstanding_amount' => round($totalOutstanding, 2),
                'overdue_amount' => round($totalOverdue, 2),
                'days_overdue' => $maxDaysOverdue,
                'overdue_invoice_count' => $overdueCount,
                'suspension_eligibility_date' => ($maxDaysOverdue >= 15) ? now()->toDateString() : null,
                'risk_level' => $riskLevel,
            ]);

            return $collAccount;
        });
    }

    public function calculateArAgingBuckets(): array
    {
        $accounts = CollectionAccount::where('total_outstanding_amount', '>', 0)->get();

        $buckets = [
            'CURRENT' => 0.00,
            '1_7_DAYS' => 0.00,
            '8_15_DAYS' => 0.00,
            '16_30_DAYS' => 0.00,
            '31_60_DAYS' => 0.00,
            '61_90_DAYS' => 0.00,
            '90_PLUS_DAYS' => 0.00,
        ];

        foreach ($accounts as $acc) {
            $days = $acc->days_overdue;
            $amt = (float)$acc->total_outstanding_amount;

            if ($days == 0) $buckets['CURRENT'] += $amt;
            elseif ($days <= 7) $buckets['1_7_DAYS'] += $amt;
            elseif ($days <= 15) $buckets['8_15_DAYS'] += $amt;
            elseif ($days <= 30) $buckets['16_30_DAYS'] += $amt;
            elseif ($days <= 60) $buckets['31_60_DAYS'] += $amt;
            elseif ($days <= 90) $buckets['61_90_DAYS'] += $amt;
            else $buckets['90_PLUS_DAYS'] += $amt;
        }

        return $buckets;
    }
}
