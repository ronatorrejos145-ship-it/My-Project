<?php

namespace App\Services;

use App\Models\Item;
use App\Models\StockBalance;
use App\Models\Stocktake;
use App\Models\StocktakeItem;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StocktakeService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService,
        protected InventoryService $inventoryService
    ) {}

    public function createStocktake(Warehouse $warehouse, string $title, array $itemCounts, ?int $userId = null): Stocktake
    {
        return DB::transaction(function () use ($warehouse, $title, $itemCounts, $userId) {
            $num = $this->numberSequenceService->getNextNumber('STOCKTAKE');

            $stocktake = Stocktake::create([
                'stocktake_number' => $num,
                'warehouse_id' => $warehouse->id,
                'title' => $title,
                'stocktake_date' => now()->toDateString(),
                'status' => 'REVIEW',
                'conducted_by' => $userId,
            ]);

            foreach ($itemCounts as $countData) {
                $item = Item::findOrFail($countData['item_id']);
                $counted = (float) $countData['counted_qty'];

                $balance = StockBalance::where('item_id', $item->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->first();

                $systemQty = $balance ? (float) $balance->quantity_on_hand : 0.00;
                $variance = $counted - $systemQty;

                StocktakeItem::create([
                    'stocktake_id' => $stocktake->id,
                    'item_id' => $item->id,
                    'system_qty' => $systemQty,
                    'counted_qty' => $counted,
                    'variance_qty' => $variance,
                    'reason' => $countData['reason'] ?? null,
                ]);
            }

            return $stocktake;
        });
    }

    public function approveAndReconcile(Stocktake $stocktake, ?int $userId = null): Stocktake
    {
        return DB::transaction(function () use ($stocktake, $userId) {
            $stocktake = Stocktake::where('id', $stocktake->id)->lockForUpdate()->firstOrFail();

            if ($stocktake->status === 'COMPLETED') {
                throw new InvalidArgumentException("Stocktake {$stocktake->stocktake_number} is already reconciled and completed.");
            }

            foreach ($stocktake->items as $stItem) {
                $item = $stItem->item;
                $variance = (float) $stItem->variance_qty;

                if ($variance > 0) {
                    $this->inventoryService->recordMovement(
                        $item,
                        $stocktake->warehouse,
                        'ADJUSTMENT_IN',
                        $variance,
                        null,
                        'Stocktake',
                        $stocktake->id,
                        'Stocktake adjustment gain',
                        null,
                        $userId
                    );
                } elseif ($variance < 0) {
                    $this->inventoryService->recordMovement(
                        $item,
                        $stocktake->warehouse,
                        'ADJUSTMENT_OUT',
                        abs($variance),
                        null,
                        'Stocktake',
                        $stocktake->id,
                        'Stocktake adjustment loss',
                        null,
                        $userId
                    );
                }
            }

            $stocktake->update([
                'status' => 'COMPLETED',
                'approved_by' => $userId,
            ]);

            AuditLogService::log(
                'APPROVE_STOCKTAKE',
                'inventory',
                $stocktake,
                ['status' => 'REVIEW'],
                ['status' => 'COMPLETED']
            );

            return $stocktake;
        });
    }
}
