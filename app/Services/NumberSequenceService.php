<?php

namespace App\Services;

use App\Models\NumberSequence;
use Illuminate\Support\Facades\DB;
use Exception;

class NumberSequenceService
{
    /**
     * Generate the next formatted number sequence atomically with a database lock.
     *
     * @param string $code (e.g. CUSTOMER, ACCOUNT, INVOICE, RECEIPT, WORK_ORDER, TICKET, ASSET, TOOL)
     * @param string|null $branchCode Optional branch prefix override
     * @return string Formatted sequence (e.g. CUST-2025-000001 or INV-000123)
     */
    public function getNextNumber(string $code, ?string $branchCode = null): string
    {
        return DB::transaction(function () use ($code, $branchCode) {
            $sequence = NumberSequence::where('code', $code)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                // Auto-create sequence if missing
                $sequence = NumberSequence::create([
                    'code' => $code,
                    'name' => ucwords(str_replace('_', ' ', strtolower($code))) . ' Sequence',
                    'prefix' => strtoupper(substr($code, 0, 4)) . '-',
                    'suffix' => '',
                    'current_number' => 0,
                    'padding' => 6,
                    'reset_period' => 'NEVER',
                    'status' => 'ACTIVE',
                ]);

                // Re-lock
                $sequence = NumberSequence::where('id', $sequence->id)->lockForUpdate()->first();
            }

            // Check reset period logic
            $today = date('Y-m-d');
            $year = date('Y');
            $month = date('Y-m');

            if ($sequence->reset_period === 'YEARLY' && $sequence->last_reset_date && substr($sequence->last_reset_date, 0, 4) !== $year) {
                $sequence->current_number = 0;
            } elseif ($sequence->reset_period === 'MONTHLY' && $sequence->last_reset_date && substr($sequence->last_reset_date, 0, 7) !== $month) {
                $sequence->current_number = 0;
            } elseif ($sequence->reset_period === 'DAILY' && $sequence->last_reset_date && $sequence->last_reset_date !== $today) {
                $sequence->current_number = 0;
            }

            $nextVal = $sequence->current_number + 1;
            $sequence->current_number = $nextVal;
            $sequence->last_reset_date = $today;
            $sequence->save();

            $paddedNumber = str_pad((string)$nextVal, $sequence->padding, '0', STR_PAD_LEFT);
            $prefix = $sequence->prefix;

            if ($branchCode && $sequence->is_branch_aware) {
                $prefix = rtrim($branchCode, '-') . '-' . $prefix;
            }

            return $prefix . $paddedNumber . $sequence->suffix;
        });
    }
}
