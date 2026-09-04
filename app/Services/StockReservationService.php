<?php

namespace App\Services;

use App\Models\Item;
use App\Models\StockBalance;
use App\Models\StockReservation;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockReservationService
{
    public function __construct(protected NumberSequenceService $numberSequenceService) {}

    public function reserveStock(
        Item $item,
        Warehouse $warehouse,
        float $quantity,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $userId = null
    ): StockReservation {
        return DB::transaction(function () use ($item, $warehouse, $quantity, $referenceType, $referenceId, $notes, $userId) {
            $balance = StockBalance::where('item_id', $item->id)
                ->where('warehouse_id', $warehouse->id)
                ->lockForUpdate()
                ->first();

            $onHand = $balance ? (float) $balance->quantity_on_hand : 0.0;
            $reserved = $balance ? (float) $balance->quantity_reserved : 0.0;
            $available = $onHand - $reserved;

            if ($available < $quantity) {
                throw new InvalidArgumentException("INSUFFICIENT AVAILABLE STOCK: Item '{$item->name}' available stock ({$available}) is less than reservation request ({$quantity}).");
            }

            if (!$balance) {
                $balance = StockBalance::create([
                    'item_id' => $item->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity_on_hand' => 0.00,
                    'quantity_reserved' => 0.00,
                ]);
            }

            $balance->increment('quantity_reserved', $quantity);

            $resNum = $this->numberSequenceService->getNextNumber('RESERVATION');

            $reservation = StockReservation::create([
                'reservation_number' => $resNum,
                'item_id' => $item->id,
                'warehouse_id' => $warehouse->id,
                'quantity_reserved' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'status' => 'RESERVED',
                'expires_at' => now()->addDays(7),
                'created_by' => $userId,
                'notes' => $notes,
            ]);

            AuditLogService::log(
                'RESERVE_STOCK',
                'inventory',
                $reservation,
                null,
                $reservation->toArray()
            );

            return $reservation;
        });
    }

    public function releaseReservation(StockReservation $reservation, ?int $userId = null): StockReservation
    {
        return DB::transaction(function () use ($reservation, $userId) {
            $reservation = StockReservation::where('id', $reservation->id)->lockForUpdate()->firstOrFail();

            if ($reservation->status !== 'RESERVED') {
                return $reservation;
            }

            $balance = StockBalance::where('item_id', $reservation->item_id)
                ->where('warehouse_id', $reservation->warehouse_id)
                ->lockForUpdate()
                ->first();

            if ($balance) {
                $balance->decrement('quantity_reserved', min($balance->quantity_reserved, $reservation->quantity_reserved));
            }

            $reservation->update(['status' => 'RELEASED']);

            AuditLogService::log(
                'RELEASE_RESERVATION',
                'inventory',
                $reservation,
                ['status' => 'RESERVED'],
                ['status' => 'RELEASED']
            );

            return $reservation;
        });
    }
}
