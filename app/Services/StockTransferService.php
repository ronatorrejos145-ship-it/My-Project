<?php

namespace App\Services;

use App\Models\InventoryTransfer;
use App\Models\InventoryTransferItem;
use App\Models\Item;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockTransferService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService,
        protected InventoryService $inventoryService
    ) {}

    public function initiateTransfer(Warehouse $source, Warehouse $destination, array $items, ?string $notes = null, ?int $userId = null): InventoryTransfer
    {
        return DB::transaction(function () use ($source, $destination, $items, $notes, $userId) {
            $transferNumber = $this->numberSequenceService->getNextNumber('INV_TRANSFER');

            $transfer = InventoryTransfer::create([
                'transfer_number' => $transferNumber,
                'source_warehouse_id' => $source->id,
                'destination_warehouse_id' => $destination->id,
                'status' => 'IN_TRANSIT',
                'requested_by' => $userId,
                'dispatched_by' => $userId,
                'dispatched_at' => now(),
                'notes' => $notes,
            ]);

            foreach ($items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                $qty = (float) $itemData['quantity'];

                // Deduct from source warehouse
                $this->inventoryService->recordMovement(
                    $item,
                    $source,
                    'TRANSFER_OUT',
                    $qty,
                    null,
                    'InventoryTransfer',
                    $transfer->id,
                    'Transfer dispatched to ' . $destination->name,
                    null,
                    $userId
                );

                InventoryTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'item_id' => $item->id,
                    'requested_qty' => $qty,
                    'dispatched_qty' => $qty,
                    'received_qty' => 0.00,
                    'unit' => $item->unit ?? 'pcs',
                ]);
            }

            AuditLogService::log(
                'INITIATE_STOCK_TRANSFER',
                'inventory',
                $transfer,
                null,
                $transfer->toArray()
            );

            return $transfer;
        });
    }

    public function receiveTransfer(InventoryTransfer $transfer, ?int $userId = null): InventoryTransfer
    {
        return DB::transaction(function () use ($transfer, $userId) {
            $transfer = InventoryTransfer::where('id', $transfer->id)->lockForUpdate()->firstOrFail();

            if ($transfer->status === 'RECEIVED') {
                throw new InvalidArgumentException("Transfer {$transfer->transfer_number} is already received.");
            }

            foreach ($transfer->items as $transferItem) {
                $item = $transferItem->item;
                $qty = (float) $transferItem->dispatched_qty;

                // Add stock to destination warehouse
                $this->inventoryService->recordMovement(
                    $item,
                    $transfer->destinationWarehouse,
                    'TRANSFER_IN',
                    $qty,
                    null,
                    'InventoryTransfer',
                    $transfer->id,
                    'Transfer received from ' . $transfer->sourceWarehouse->name,
                    null,
                    $userId
                );

                $transferItem->update(['received_qty' => $qty]);
            }

            $transfer->update([
                'status' => 'RECEIVED',
                'received_by' => $userId,
                'received_at' => now(),
            ]);

            AuditLogService::log(
                'RECEIVE_STOCK_TRANSFER',
                'inventory',
                $transfer,
                ['status' => 'IN_TRANSIT'],
                ['status' => 'RECEIVED']
            );

            return $transfer;
        });
    }
}
