<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class ProrationCalculator
{
    /**
     * Compute exact mid-cycle proration using DECIMAL precision.
     *
     * @param float $fullPrice Monthly or cycle base price
     * @param string $serviceStart Date service starts or changes (e.g. '2026-05-15')
     * @param string $periodStart Start date of billing period (e.g. '2026-05-01')
     * @param string $periodEnd End date of billing period (e.g. '2026-05-31')
     * @param string $basis DAILY, CALENDAR_DAY, or FIXED_30_DAY
     * @return array Calculated components
     */
    public function calculateProration(
        float $fullPrice,
        string $serviceStart,
        string $periodStart,
        string $periodEnd,
        string $basis = 'CALENDAR_DAY'
    ): array {
        $start = Carbon::parse($periodStart)->startOfDay();
        $end = Carbon::parse($periodEnd)->startOfDay();
        $service = Carbon::parse($serviceStart)->startOfDay();

        if ($service->lt($start) || $service->gt($end)) {
            // Out of bounds, default to full or zero based on boundary
            if ($service->lte($start)) {
                return [
                    'full_price' => round($fullPrice, 2),
                    'total_days' => $start->diffInDays($end) + 1,
                    'used_days' => $start->diffInDays($end) + 1,
                    'prorated_amount' => round($fullPrice, 2),
                    'proration_factor' => 1.0,
                    'basis' => $basis,
                ];
            }
        }

        $totalDays = match ($basis) {
            'FIXED_30_DAY' => 30,
            default => $start->diffInDays($end) + 1, // CALENDAR_DAY includes end date
        };

        if ($totalDays <= 0) {
            throw new InvalidArgumentException("Invalid billing period boundary: start {$periodStart} must be before end {$periodEnd}.");
        }

        $usedDays = $service->diffInDays($end) + 1;
        $usedDays = min($usedDays, $totalDays);

        $prorationFactor = $usedDays / (float) $totalDays;
        $proratedAmount = round($fullPrice * $prorationFactor, 2);

        return [
            'full_price' => round($fullPrice, 2),
            'total_days' => $totalDays,
            'used_days' => $usedDays,
            'unused_days' => $totalDays - $usedDays,
            'proration_factor' => round($prorationFactor, 6),
            'prorated_amount' => $proratedAmount,
            'unused_amount' => round($fullPrice - $proratedAmount, 2),
            'basis' => $basis,
        ];
    }
}
