<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetStatusHistory;
use App\Models\AssetTransfer;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetTransferService
{
    public function __construct(protected NumberSequenceService $numberSequenceService) {}

    public function initiateTransfer(
        Asset $asset,
        string $destinationType,
        int $destinationId,
        ?string $notes = null,
        ?int $userId = null
    ): AssetTransfer {
        return DB::transaction(function () use ($asset, $destinationType, $destinationId, $notes, $userId) {
            /** @var Asset $asset */
            $asset = Asset::where('id', $asset->id)->lockForUpdate()->firstOrFail();

            if (in_array($asset->current_status, ['DISPOSED', 'RETIRED', 'STOLEN'])) {
                throw new InvalidArgumentException("Cannot transfer asset {$asset->asset_tag} with status {$asset->current_status}.");
            }

            $transferNumber = $this->numberSequenceService->getNextNumber('TRANSFER');

            if ($asset->assigned_customer_id) {
                $sourceType = 'App\\Models\\Customer';
                $sourceId = $asset->assigned_customer_id;
            } elseif ($asset->assigned_employee_id) {
                $sourceType = 'App\\Models\\Employee';
                $sourceId = $asset->assigned_employee_id;
            } else {
                $sourceType = 'App\\Models\\Warehouse';
                $sourceId = 1;
            }

            $transfer = AssetTransfer::create([
                'transfer_number' => $transferNumber,
                'asset_id' => $asset->id,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'destination_type' => $destinationType,
                'destination_id' => $destinationId,
                'status' => 'IN_TRANSIT',
                'authorized_by' => $userId,
                'transferred_by' => $userId,
                'transferred_at' => now(),
                'condition_on_transfer' => $asset->condition,
                'notes' => $notes,
            ]);

            $oldStatus = $asset->current_status;
            $asset->update([
                'current_status' => 'IN_TRANSIT',
                'current_location' => "In Transit to {$destinationType} #{$destinationId}",
            ]);

            AssetStatusHistory::create([
                'asset_id' => $asset->id,
                'old_status' => $oldStatus,
                'new_status' => 'IN_TRANSIT',
                'changed_by' => $userId,
                'reason' => "Transfer initiated (#{$transferNumber})",
            ]);

            AuditLogService::log(
                'INITIATE_ASSET_TRANSFER',
                'assets',
                $asset,
                ['status' => $oldStatus],
                ['status' => 'IN_TRANSIT', 'transfer_number' => $transferNumber]
            );

            return $transfer;
        });
    }

    public function completeTransfer(AssetTransfer $transfer, ?int $userId = null): AssetTransfer
    {
        return DB::transaction(function () use ($transfer, $userId) {
            $transfer = AssetTransfer::where('id', $transfer->id)->lockForUpdate()->firstOrFail();

            if ($transfer->status === 'COMPLETED') {
                throw new InvalidArgumentException("Transfer {$transfer->transfer_number} is already completed.");
            }

            $asset = Asset::where('id', $transfer->asset_id)->lockForUpdate()->firstOrFail();

            $transfer->update([
                'status' => 'COMPLETED',
                'received_by' => $userId,
                'received_at' => now(),
            ]);

            $newLocation = "Location: {$transfer->destination_type} #{$transfer->destination_id}";
            $asset->update([
                'current_status' => 'AVAILABLE',
                'current_location' => $newLocation,
            ]);

            AssetStatusHistory::create([
                'asset_id' => $asset->id,
                'old_status' => 'IN_TRANSIT',
                'new_status' => 'AVAILABLE',
                'old_location' => $transfer->source_type,
                'new_location' => $newLocation,
                'changed_by' => $userId,
                'reason' => "Transfer completed (#{$transfer->transfer_number})",
            ]);

            AuditLogService::log(
                'COMPLETE_ASSET_TRANSFER',
                'assets',
                $asset,
                ['status' => 'IN_TRANSIT'],
                ['status' => 'AVAILABLE', 'location' => $newLocation]
            );

            return $transfer;
        });
    }
}
