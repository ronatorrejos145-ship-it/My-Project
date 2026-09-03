<?php

namespace App\Services;

use App\Models\Item;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Support\Facades\DB;

class PurchaseRequestService
{
    public function __construct(protected NumberSequenceService $numberSequenceService) {}

    public function createPurchaseRequest(array $data, array $items, int $userId): PurchaseRequest
    {
        return DB::transaction(function () use ($data, $items, $userId) {
            $prNum = $this->numberSequenceService->getNextNumber('PURCHASE_REQUEST');

            $estimatedTotal = 0.00;
            foreach ($items as $itemData) {
                $estimatedTotal += ((float) $itemData['quantity']) * ((float) ($itemData['estimated_unit_cost'] ?? 0));
            }

            $pr = PurchaseRequest::create([
                'pr_number' => $prNum,
                'requester_id' => $userId,
                'branch_id' => $data['branch_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'priority' => $data['priority'] ?? 'NORMAL',
                'status' => 'SUBMITTED',
                'required_date' => $data['required_date'] ?? now()->addDays(7)->toDateString(),
                'estimated_total' => $estimatedTotal,
                'justification' => $data['justification'] ?? null,
            ]);

            foreach ($items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                $qty = (float) $itemData['quantity'];
                $cost = (float) ($itemData['estimated_unit_cost'] ?? $item->unit_cost);

                PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->id,
                    'item_id' => $item->id,
                    'quantity' => $qty,
                    'estimated_unit_cost' => $cost,
                    'estimated_subtotal' => $qty * $cost,
                ]);
            }

            AuditLogService::log(
                'CREATE_PURCHASE_REQUEST',
                'procurement',
                $pr,
                null,
                $pr->toArray()
            );

            return $pr;
        });
    }

    public function approvePurchaseRequest(PurchaseRequest $pr, int $userId): PurchaseRequest
    {
        return DB::transaction(function () use ($pr, $userId) {
            $pr = PurchaseRequest::where('id', $pr->id)->lockForUpdate()->firstOrFail();

            $pr->update([
                'status' => 'APPROVED',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            AuditLogService::log(
                'APPROVE_PURCHASE_REQUEST',
                'procurement',
                $pr,
                ['status' => 'SUBMITTED'],
                ['status' => 'APPROVED']
            );

            return $pr;
        });
    }
}
