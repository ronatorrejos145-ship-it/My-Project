<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetDisposal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetDisposalService
{
    public function __construct(protected NumberSequenceService $numberSequenceService) {}

    public function disposeAsset(Asset $asset, string $method, float $salePrice = 0.00, ?string $certificateNumber = null, ?string $notes = null, ?int $userId = null): AssetDisposal
    {
        return DB::transaction(function () use ($asset, $method, $salePrice, $certificateNumber, $notes, $userId) {
            $asset = Asset::where('id', $asset->id)->lockForUpdate()->firstOrFail();

            if ($asset->current_status !== 'RETIRED') {
                throw new InvalidArgumentException("Asset {$asset->asset_tag} must be in RETIRED status prior to formal disposal.");
            }

            $disposalNum = $this->numberSequenceService->getNextNumber('DISPOSAL');

            $disposal = AssetDisposal::create([
                'asset_id' => $asset->id,
                'disposal_number' => $disposalNum,
                'disposal_method' => $method,
                'disposed_by' => $userId,
                'authorized_by' => $userId,
                'disposed_at' => now(),
                'sale_price' => $salePrice,
                'certificate_number' => $certificateNumber,
                'notes' => $notes,
            ]);

            $asset->update([
                'current_status' => 'DISPOSED',
                'current_location' => 'Disposed / Scrapped',
            ]);

            AuditLogService::log(
                'DISPOSE_ASSET',
                'assets',
                $asset,
                ['status' => 'RETIRED'],
                ['status' => 'DISPOSED', 'disposal_number' => $disposalNum]
            );

            return $disposal;
        });
    }
}
