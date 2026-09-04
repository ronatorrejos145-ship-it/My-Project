<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\WorkOrderEquipmentReplacement;
use App\Models\Asset;
use App\Models\Customer;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class EquipmentReplacementService
{
    public function replaceEquipment(
        WorkOrder $workOrder,
        ?int $oldAssetId,
        ?string $oldSerial,
        ?string $oldMac,
        ?int $newAssetId,
        ?string $newSerial,
        ?string $newMac,
        ?string $reason = null,
        ?int $userId = null
    ): WorkOrderEquipmentReplacement {
        return DB::transaction(function () use ($workOrder, $oldAssetId, $oldSerial, $oldMac, $newAssetId, $newSerial, $newMac, $reason, $userId) {
            // Decommission old asset if present
            if ($oldAssetId) {
                $oldAsset = Asset::find($oldAssetId);
                if ($oldAsset) {
                    $oldAsset->current_status = 'FAULTY_RETURNED';
                    $oldAsset->assigned_customer_id = null;
                    $oldAsset->save();
                }
            }

            // Assign new asset if present
            if ($newAssetId) {
                $newAsset = Asset::find($newAssetId);
                if ($newAsset) {
                    $newAsset->current_status = 'ASSIGNED';
                    $newAsset->assigned_customer_id = $workOrder->customer_id;
                    $newAsset->save();
                }
            }

            $replacement = WorkOrderEquipmentReplacement::create([
                'work_order_id' => $workOrder->id,
                'customer_id' => $workOrder->customer_id,
                'subscription_id' => $workOrder->subscription_id,
                'old_asset_id' => $oldAssetId,
                'old_serial_number' => $oldSerial,
                'old_mac_address' => $oldMac,
                'new_asset_id' => $newAssetId,
                'new_serial_number' => $newSerial,
                'new_mac_address' => $newMac,
                'replacement_reason' => $reason,
                'disposed_or_returned_status' => 'RETURNED_TO_WAREHOUSE',
                'replaced_by_user_id' => $userId,
                'replaced_at' => now(),
            ]);

            return $replacement;
        });
    }
}
