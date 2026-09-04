<?php

namespace App\Services;

use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public function __construct(protected NumberSequenceService $numberSequenceService) {}

    public function createPurchaseOrder(Supplier $supplier, Warehouse $warehouse, array $items, ?PurchaseRequest $pr = null, ?int $userId = null): PurchaseOrder
    {
        return DB::transaction(function () use ($supplier, $warehouse, $items, $pr, $userId) {
            $poNum = $this->numberSequenceService->getNextNumber('PURCHASE_ORDER');

            $subtotal = 0.00;
            foreach ($items as $itemData) {
                $subtotal += ((float) $itemData['ordered_qty']) * ((float) $itemData['unit_price']);
            }

            $po = PurchaseOrder::create([
                'po_number' => $poNum,
                'supplier_id' => $supplier->id,
                'purchase_request_id' => $pr?->id,
                'warehouse_id' => $warehouse->id,
                'order_date' => now()->toDateString(),
                'expected_delivery' => now()->addDays(7)->toDateString(),
                'subtotal' => $subtotal,
                'tax_amount' => 0.00,
                'discount_amount' => 0.00,
                'total_amount' => $subtotal,
                'status' => 'APPROVED',
                'created_by' => $userId,
            ]);

            foreach ($items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                $qty = (float) $itemData['ordered_qty'];
                $price = (float) $itemData['unit_price'];

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'item_id' => $item->id,
                    'ordered_qty' => $qty,
                    'received_qty' => 0.00,
                    'unit_price' => $price,
                    'line_total' => $qty * $price,
                ]);
            }

            if ($pr) {
                $pr->update(['status' => 'PO_CREATED']);
            }

            AuditLogService::log(
                'CREATE_PURCHASE_ORDER',
                'procurement',
                $po,
                null,
                $po->toArray()
            );

            return $po;
        });
    }
}
