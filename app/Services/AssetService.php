<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetStatusHistory;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetService
{
    public function transitionStatus(Asset $asset, string $newStatus, ?string $newCondition = null, ?string $newLocation = null, ?string $reason = null, ?int $userId = null): Asset
    {
        return DB::transaction(function () use ($asset, $newStatus, $newCondition, $newLocation, $reason, $userId) {
            $asset = Asset::where('id', $asset->id)->lockForUpdate()->firstOrFail();

            $oldStatus = $asset->current_status;
            $oldCondition = $asset->condition;
            $oldLocation = $asset->current_location;

            $asset->update([
                'current_status' => $newStatus,
                'condition' => $newCondition ?? $asset->condition,
                'current_location' => $newLocation ?? $asset->current_location,
            ]);

            AssetStatusHistory::create([
                'asset_id' => $asset->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'old_condition' => $oldCondition,
                'new_condition' => $newCondition ?? $oldCondition,
                'old_location' => $oldLocation,
                'new_location' => $newLocation ?? $oldLocation,
                'changed_by' => $userId,
                'reason' => $reason ?? 'Status updated',
            ]);

            AuditLogService::log(
                'ASSET_STATUS_CHANGE',
                'assets',
                $asset,
                ['status' => $oldStatus, 'condition' => $oldCondition],
                ['status' => $newStatus, 'condition' => $newCondition ?? $oldCondition]
            );

            return $asset;
        });
    }

    public function normalizeMacAddress(?string $mac): ?string
    {
        if (empty($mac)) {
            return null;
        }

        $cleaned = strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', $mac));
        if (strlen($cleaned) !== 12) {
            throw new InvalidArgumentException("Invalid MAC address format: '{$mac}'. Must contain 12 hexadecimal characters.");
        }

        return implode(':', str_split($cleaned, 2));
    }

    public function normalizeSerialNumber(?string $serial): ?string
    {
        if (empty($serial)) {
            return null;
        }

        return strtoupper(trim($serial));
    }
}
