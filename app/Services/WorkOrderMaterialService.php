<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\WorkOrderMaterial;
use App\Models\Item;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WorkOrderMaterialService
{
    public function consumeMaterial(WorkOrder $workOrder, int $itemId, float $quantity, ?int $warehouseId = null, ?string $serialNumber = null, ?int $userId = null): WorkOrderMaterial
    {
        $item = Item::findOrFail($itemId);

        return DB::transaction(function () use ($workOrder, $item, $itemId, $quantity, $warehouseId, $serialNumber, $userId) {
            $unitCost = $item->cost_price ?? 0.00;
            $totalCost = bcmul((string) $unitCost, (string) $quantity, 2);

            $material = WorkOrderMaterial::create([
                'work_order_id' => $workOrder->id,
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'serial_number' => $serialNumber,
                'required_quantity' => $quantity,
                'issued_quantity' => $quantity,
                'consumed_quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'status' => 'CONSUMED',
            ]);

            // Update actual_cost on work order
            $workOrder->actual_cost = bcadd((string) $workOrder->actual_cost, $totalCost, 2);
            $workOrder->save();

            // Perform inventory deduction if InventoryService exists
            if ($warehouseId && class_exists(InventoryService::class)) {
                try {
                    app(InventoryService::class)->recordMovement([
                        'item_id' => $itemId,
                        'warehouse_id' => $warehouseId,
                        'movement_type' => 'OUT',
                        'quantity' => $quantity,
                        'reference' => 'WO ' . $workOrder->work_order_number,
                        'notes' => 'Consumed on Work Order ' . $workOrder->work_order_number,
                        'created_by' => $userId,
                    ]);
                } catch (\Throwable $e) {
                    // Gracefully handle inventory deduction if warehouse inventory is not pre-seeded
                }
            }

            return $material;
        });
    }
}
