<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\InstallationEquipment;
use App\Models\InstallationWorkOrder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InstallationEquipmentService
{
    public function assignEquipment(
        InstallationWorkOrder $workOrder,
        string $equipmentType,
        ?int $assetId = null,
        ?string $modelName = null,
        ?string $serialNumber = null,
        ?string $macAddress = null,
        ?string $notes = null,
        ?int $userId = null
    ): InstallationEquipment {
        return DB::transaction(function () use ($workOrder, $equipmentType, $assetId, $modelName, $serialNumber, $macAddress, $notes, $userId) {
            /** @var InstallationWorkOrder $workOrder */
            $workOrder = InstallationWorkOrder::where('id', $workOrder->id)->lockForUpdate()->firstOrFail();

            if ($assetId) {
                $asset = Asset::where('id', $assetId)->lockForUpdate()->firstOrFail();

                // Check double assignment
                $alreadyAssigned = InstallationEquipment::where('asset_id', $assetId)
                    ->whereHas('installation', function ($q) {
                        $q->whereNotIn('status', ['FAILED', 'CANCELLED']);
                    })
                    ->where('installation_id', '!=', $workOrder->id)
                    ->exists();

                if ($alreadyAssigned) {
                    throw new InvalidArgumentException("Asset ID {$assetId} ({$asset->asset_tag}) is already assigned to another active installation work order.");
                }

                $serialNumber = $serialNumber ?? $asset->serial_number;
                $macAddress = $macAddress ?? $asset->mac_address;
                $modelName = $modelName ?? $asset->name;

                // Update asset location/status handoff
                $asset->update([
                    'current_status' => 'IN_USE',
                    'assigned_customer_id' => $workOrder->customer_id,
                    'current_location' => $workOrder->address->full_address ?? 'Customer Installation Site',
                ]);
            }

            $equipment = InstallationEquipment::create([
                'installation_id' => $workOrder->id,
                'asset_id' => $assetId,
                'equipment_type' => $equipmentType,
                'model_name' => $modelName,
                'serial_number' => $serialNumber,
                'mac_address' => $macAddress,
                'condition_before' => 'NEW',
                'condition_after' => 'INSTALLED',
                'assigned_by' => $userId,
                'assigned_at' => now(),
                'notes' => $notes,
            ]);

            AuditLogService::log(
                'ASSIGN_EQUIPMENT',
                'installations',
                $equipment,
                null,
                $equipment->toArray()
            );

            return $equipment;
        });
    }
}
