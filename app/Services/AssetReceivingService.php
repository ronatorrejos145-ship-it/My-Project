<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetStatusHistory;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetReceivingService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService,
        protected AssetService $assetService
    ) {}

    public function receiveAsset(array $data, ?int $userId = null): Asset
    {
        return DB::transaction(function () use ($data, $userId) {
            $serial = $this->assetService->normalizeSerialNumber($data['serial_number'] ?? null);
            $mac = $this->assetService->normalizeMacAddress($data['mac_address'] ?? null);

            if ($serial) {
                $existsSerial = Asset::where('serial_number', $serial)->exists();
                if ($existsSerial) {
                    throw new InvalidArgumentException("Duplicate active serial number '{$serial}' already exists in asset master catalog.");
                }
            }

            if ($mac) {
                $existsMac = Asset::where('mac_address', $mac)->whereNotIn('current_status', ['DISPOSED', 'RETIRED'])->exists();
                if ($existsMac) {
                    throw new InvalidArgumentException("Duplicate active MAC address '{$mac}' already exists in asset master catalog.");
                }
            }

            $assetTag = $this->numberSequenceService->getNextNumber('ASSET');

            $warehouseName = 'Central Warehouse';
            if (!empty($data['warehouse_id'])) {
                $warehouse = Warehouse::find($data['warehouse_id']);
                if ($warehouse) {
                    $warehouseName = $warehouse->name;
                }
            }

            $asset = Asset::create([
                'asset_tag' => $assetTag,
                'asset_category_id' => $data['asset_category_id'],
                'asset_model_id' => $data['asset_model_id'] ?? null,
                'serial_number' => $serial,
                'mac_address' => $mac,
                'manufacturer' => $data['manufacturer'] ?? null,
                'purchase_date' => $data['purchase_date'] ?? now()->toDateString(),
                'purchase_cost' => $data['purchase_cost'] ?? 0.00,
                'warranty_start' => $data['warranty_start'] ?? now()->toDateString(),
                'warranty_end' => $data['warranty_end'] ?? now()->addYear()->toDateString(),
                'current_status' => 'AVAILABLE',
                'current_location' => $warehouseName,
                'condition' => $data['condition'] ?? 'NEW',
                'notes' => $data['notes'] ?? 'Received serialized asset into inventory.',
            ]);

            AssetStatusHistory::create([
                'asset_id' => $asset->id,
                'old_status' => null,
                'new_status' => 'AVAILABLE',
                'old_condition' => null,
                'new_condition' => $asset->condition,
                'old_location' => null,
                'new_location' => $asset->current_location,
                'changed_by' => $userId,
                'reason' => 'Asset received into inventory.',
            ]);

            AuditLogService::log(
                'RECEIVE_ASSET',
                'assets',
                $asset,
                null,
                $asset->toArray()
            );

            return $asset;
        });
    }
}
