<?php

namespace App\Services;

use App\Models\Item;
use App\Models\StockBalance;

class ReorderService
{
    public function getReorderAlerts(): array
    {
        $items = Item::where('status', 'ACTIVE')->get();
        $alerts = [];

        foreach ($items as $item) {
            $totalOnHand = StockBalance::where('item_id', $item->id)->sum('quantity_on_hand');
            $totalReserved = StockBalance::where('item_id', $item->id)->sum('quantity_reserved');
            $available = $totalOnHand - $totalReserved;

            $status = 'NORMAL';
            if ($available <= 0) {
                $status = 'OUT_OF_STOCK';
            } elseif ($item->reorder_level > 0 && $available <= $item->reorder_level) {
                $status = 'CRITICAL';
            } elseif ($item->minimum_stock > 0 && $available <= $item->minimum_stock) {
                $status = 'LOW';
            }

            if ($status !== 'NORMAL') {
                $alerts[] = [
                    'item' => $item,
                    'available_stock' => $available,
                    'reorder_level' => $item->reorder_level,
                    'minimum_stock' => $item->minimum_stock,
                    'status' => $status,
                ];
            }
        }

        return $alerts;
    }
}
