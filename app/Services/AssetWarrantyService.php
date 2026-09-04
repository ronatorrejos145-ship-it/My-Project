<?php

namespace App\Services;

use App\Models\Asset;
use Carbon\Carbon;

class AssetWarrantyService
{
    public function getWarrantyStatus(Asset $asset): array
    {
        if (!$asset->warranty_end) {
            return [
                'status' => 'NO_WARRANTY',
                'days_remaining' => 0,
                'badge' => 'gray',
            ];
        }

        $now = Carbon::now();
        $end = Carbon::parse($asset->warranty_end);

        if ($now->gt($end)) {
            return [
                'status' => 'EXPIRED',
                'days_remaining' => 0,
                'badge' => 'red',
            ];
        }

        $days = $now->diffInDays($end);
        if ($days <= 30) {
            return [
                'status' => 'EXPIRING_SOON',
                'days_remaining' => $days,
                'badge' => 'yellow',
            ];
        }

        return [
            'status' => 'ACTIVE',
            'days_remaining' => $days,
            'badge' => 'green',
        ];
    }
}
