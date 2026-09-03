<?php

namespace App\Services;

use App\Models\InstallationMaterial;
use App\Models\InstallationWorkOrder;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InstallationMaterialService
{
    public function issueMaterial(
        InstallationWorkOrder $workOrder,
        ?int $itemId,
        string $itemName,
        float $quantity,
        string $unit = 'pcs',
        ?string $notes = null,
        ?int $userId = null
    ): InstallationMaterial {
        return DB::transaction(function () use ($workOrder, $itemId, $itemName, $quantity, $unit, $notes, $userId) {
            if ($itemId) {
                $item = Item::findOrFail($itemId);
            }

            $material = InstallationMaterial::where('installation_id', $workOrder->id)
                ->where(function ($q) use ($itemId, $itemName) {
                    if ($itemId) {
                        $q->where('item_id', $itemId);
                    } else {
                        $q->where('item_name', $itemName);
                    }
                })
                ->first();

            if ($material) {
                $material->issued_qty += $quantity;
                $material->consumed_qty += $quantity;
                $material->variance_qty = $material->issued_qty - $material->consumed_qty - $material->returned_qty;
                $material->save();
            } else {
                $material = InstallationMaterial::create([
                    'installation_id' => $workOrder->id,
                    'item_id' => $itemId,
                    'item_name' => $itemName,
                    'unit' => $unit,
                    'planned_qty' => $quantity,
                    'issued_qty' => $quantity,
                    'consumed_qty' => $quantity,
                    'returned_qty' => 0,
                    'damaged_qty' => 0,
                    'variance_qty' => 0,
                    'notes' => $notes,
                ]);
            }

            AuditLogService::log(
                'ISSUE_MATERIAL',
                'installations',
                $material,
                null,
                $material->toArray()
            );

            return $material;
        });
    }

    public function returnMaterial(
        InstallationWorkOrder $workOrder,
        int $materialId,
        float $returnedQty,
        float $damagedQty = 0,
        ?string $notes = null,
        ?int $userId = null
    ): InstallationMaterial {
        return DB::transaction(function () use ($workOrder, $materialId, $returnedQty, $damagedQty, $notes, $userId) {
            $material = InstallationMaterial::where('installation_id', $workOrder->id)
                ->where('id', $materialId)
                ->firstOrFail();

            if ($returnedQty + $damagedQty > $material->issued_qty) {
                throw new InvalidArgumentException("Returned quantity exceeds issued quantity.");
            }

            $material->returned_qty += $returnedQty;
            $material->damaged_qty += $damagedQty;
            $material->consumed_qty = max(0, $material->issued_qty - $material->returned_qty - $material->damaged_qty);
            $material->variance_qty = $material->issued_qty - ($material->consumed_qty + $material->returned_qty + $material->damaged_qty);
            if ($notes) {
                $material->notes = $material->notes ? $material->notes . ' | ' . $notes : $notes;
            }
            $material->save();

            AuditLogService::log(
                'RETURN_MATERIAL',
                'installations',
                $material,
                null,
                $material->toArray()
            );

            return $material;
        });
    }
}
