<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GoodsReceivingService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService,
        protected InventoryService $inventoryService
    ) {}

    public function receiveGoods(PurchaseOrder $po, array $receivedItems, ?string $deliveryDoc = null, ?int $userId = null): GoodsReceipt
    {
        return DB::transaction(function () use ($po, $receivedItems, $deliveryDoc, $userId) {
            /** @var PurchaseOrder $po */
            $po = PurchaseOrder::where('id', $po->id)->lockForUpdate()->firstOrFail();

            if (in_array($po->status, ['CANCELLED', 'RECEIVED'])) {
                throw new InvalidArgumentException("Cannot receive goods for PO {$po->po_number} with status {$po->status}.");
            }

            $receiptNum = $this->numberSequenceService->getNextNumber('GOODS_RECEIPT');

            $receipt = GoodsReceipt::create([
                'receipt_number' => $receiptNum,
                'purchase_order_id' => $po->id,
                'warehouse_id' => $po->warehouse_id,
                'received_by' => $userId,
                'received_at' => now(),
                'delivery_document_number' => $deliveryDoc,
                'inspection_status' => 'ACCEPTED',
            ]);

            $fullyReceived = true;

            foreach ($receivedItems as $itemData) {
                $poItem = PurchaseOrderItem::where('purchase_order_id', $po->id)
                    ->where('item_id', $itemData['item_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $recvQty = (float) $itemData['received_qty'];
                $outstanding = $poItem->ordered_qty - $poItem->received_qty;

                // Over-receiving protection
                if ($recvQty > $outstanding) {
                    throw new InvalidArgumentException("OVER-RECEIVING REJECTED: Received quantity ({$recvQty}) exceeds outstanding PO quantity ({$outstanding}) for item ID {$poItem->item_id}.");
                }

                $poItem->received_qty += $recvQty;
                $poItem->save();

                if ($poItem->received_qty < $poItem->ordered_qty) {
                    $fullyReceived = false;
                }

                // Add to inventory balance & ledger
                $this->inventoryService->recordMovement(
                    $poItem->item,
                    $po->warehouse,
                    'RECEIPT',
                    $recvQty,
                    null,
                    'GoodsReceipt',
                    $receipt->id,
                    'Goods received against PO ' . $po->po_number,
                    null,
                    $userId
                );

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'item_id' => $poItem->item_id,
                    'received_qty' => $recvQty,
                    'unit_cost' => $poItem->unit_price,
                ]);
            }

            // Check overall PO status
            $allPoItems = PurchaseOrderItem::where('purchase_order_id', $po->id)->get();
            $allComplete = $allPoItems->every(fn ($pi) => $pi->received_qty >= $pi->ordered_qty);

            $po->update([
                'status' => $allComplete ? 'RECEIVED' : 'PARTIALLY_RECEIVED',
            ]);

            AuditLogService::log(
                'RECEIVE_GOODS',
                'procurement',
                $receipt,
                null,
                $receipt->toArray()
            );

            return $receipt;
        });
    }
}
