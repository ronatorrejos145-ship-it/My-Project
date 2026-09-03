<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\StockBalance;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    public function __construct(protected NumberSequenceService $numberSequenceService) {}

    public function recordMovement(
        Item $item,
        Warehouse $warehouse,
        string $transactionType, // RECEIPT, ISSUE, RETURN, TRANSFER_IN, TRANSFER_OUT, ADJUSTMENT_IN, ADJUSTMENT_OUT, DAMAGE, LOSS
        float $quantity,
        ?int $locationId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $reason = null,
        ?string $notes = null,
        ?int $userId = null
    ): InventoryTransaction {
        return DB::transaction(function () use ($item, $warehouse, $transactionType, $quantity, $locationId, $referenceType, $referenceId, $reason, $notes, $userId) {
            if ($quantity <= 0) {
                throw new InvalidArgumentException("Inventory transaction quantity must be greater than zero.");
            }

            // Lock balance row
            $balance = StockBalance::where('item_id', $item->id)
                ->where('warehouse_id', $warehouse->id)
                ->where('location_id', $locationId)
                ->lockForUpdate()
                ->first();

            if (!$balance) {
                $balance = StockBalance::create([
                    'item_id' => $item->id,
                    'warehouse_id' => $warehouse->id,
                    'location_id' => $locationId,
                    'quantity_on_hand' => 0.00,
                    'quantity_reserved' => 0.00,
                    'quantity_damaged' => 0.00,
                    'quantity_in_transit' => 0.00,
                ]);
            }

            $prevQty = (float) $balance->quantity_on_hand;
            $delta = 0.0;

            if (in_array($transactionType, ['RECEIPT', 'RETURN', 'TRANSFER_IN', 'ADJUSTMENT_IN'])) {
                $delta = $quantity;
            } elseif (in_array($transactionType, ['ISSUE', 'TRANSFER_OUT', 'ADJUSTMENT_OUT', 'DAMAGE', 'LOSS'])) {
                if ($prevQty < $quantity) {
                    throw new InvalidArgumentException("INSUFFICIENT STOCK: Warehouse '{$warehouse->name}' has {$prevQty} {$item->unit} of '{$item->name}', but requested deduction is {$quantity}.");
                }
                $delta = -$quantity;
            }

            $resultingQty = $prevQty + $delta;
            $balance->update(['quantity_on_hand' => $resultingQty]);

            $txNum = $this->numberSequenceService->getNextNumber('INV_TX');

            $tx = InventoryTransaction::create([
                'transaction_number' => $txNum,
                'item_id' => $item->id,
                'warehouse_id' => $warehouse->id,
                'location_id' => $locationId,
                'transaction_type' => $transactionType,
                'quantity' => $quantity,
                'previous_quantity' => $prevQty,
                'resulting_quantity' => $resultingQty,
                'unit' => $item->unit ?? 'pcs',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'performed_by' => $userId,
                'reason' => $reason,
                'notes' => $notes,
            ]);

            AuditLogService::log(
                'INVENTORY_MOVEMENT',
                'inventory',
                $tx,
                ['previous_quantity' => $prevQty],
                ['resulting_quantity' => $resultingQty, 'transaction_type' => $transactionType]
            );

            return $tx;
        });
    }
}
