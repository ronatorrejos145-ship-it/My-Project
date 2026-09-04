<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetReplacement;
use App\Models\Customer;
use App\Models\InstallationWorkOrder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetReplacementService
{
    public function __construct(protected AssetAssignmentService $assignmentService) {}

    public function replaceEquipment(
        Asset $oldAsset,
        Asset $newAsset,
        Customer $customer,
        ?InstallationWorkOrder $installation = null,
        string $reason = 'Equipment failure / upgrade',
        string $oldAssetCondition = 'DAMAGED',
        ?string $notes = null,
        ?int $userId = null
    ): AssetReplacement {
        return DB::transaction(function () use ($oldAsset, $newAsset, $customer, $installation, $reason, $oldAssetCondition, $notes, $userId) {
            $oldAsset = Asset::where('id', $oldAsset->id)->lockForUpdate()->firstOrFail();
            $newAsset = Asset::where('id', $newAsset->id)->lockForUpdate()->firstOrFail();

            if ($newAsset->current_status !== 'AVAILABLE') {
                throw new InvalidArgumentException("Replacement asset {$newAsset->asset_tag} must be in AVAILABLE status, currently {$newAsset->current_status}.");
            }

            // Unassign old asset
            $oldAsset->update([
                'current_status' => 'IN_REPAIR',
                'condition' => $oldAssetCondition,
                'assigned_customer_id' => null,
                'current_location' => 'RMA / Maintenance Warehouse',
            ]);

            // Assign new asset to customer
            $this->assignmentService->assignToCustomer($newAsset, $customer, "Replacement for asset {$oldAsset->asset_tag}", $userId);

            $replacement = AssetReplacement::create([
                'old_asset_id' => $oldAsset->id,
                'new_asset_id' => $newAsset->id,
                'customer_id' => $customer->id,
                'installation_id' => $installation?->id,
                'replaced_by' => $userId,
                'replaced_at' => now(),
                'reason' => $reason,
                'old_asset_condition' => $oldAssetCondition,
                'notes' => $notes,
            ]);

            AuditLogService::log(
                'REPLACE_EQUIPMENT',
                'assets',
                $replacement,
                ['old_asset_id' => $oldAsset->id],
                ['new_asset_id' => $newAsset->id, 'customer_id' => $customer->id]
            );

            return $replacement;
        });
    }
}
