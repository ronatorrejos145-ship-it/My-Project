<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetDisposal;
use App\Models\AssetRetirement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetRetirementService
{
    public function retireAsset(Asset $asset, string $reason, float $residualValue = 0.00, ?string $notes = null, ?int $userId = null): AssetRetirement
    {
        return DB::transaction(function () use ($asset, $reason, $residualValue, $notes, $userId) {
            $asset = Asset::where('id', $asset->id)->lockForUpdate()->firstOrFail();

            if ($asset->current_status === 'INSTALLED') {
                throw new InvalidArgumentException("Cannot retire actively installed asset {$asset->asset_tag}. Unassign from customer first.");
            }

            $retirement = AssetRetirement::create([
                'asset_id' => $asset->id,
                'retired_by' => $userId,
                'retired_at' => now(),
                'reason' => $reason,
                'residual_value' => $residualValue,
                'notes' => $notes,
            ]);

            $asset->update([
                'current_status' => 'RETIRED',
                'current_location' => 'Retired Asset Depot',
            ]);

            AuditLogService::log(
                'RETIRE_ASSET',
                'assets',
                $asset,
                ['status' => $asset->getOriginal('current_status')],
                ['status' => 'RETIRED']
            );

            return $retirement;
        });
    }
}
