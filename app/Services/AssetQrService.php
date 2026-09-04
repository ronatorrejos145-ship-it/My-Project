<?php

namespace App\Services;

use App\Models\Asset;

class AssetQrService
{
    public function generateQrPayload(Asset $asset): array
    {
        return [
            'asset_tag' => $asset->asset_tag,
            'category' => $asset->category->name ?? 'N/A',
            'model' => $asset->model->model_name ?? $asset->manufacturer ?? 'Generic',
            'serial_number' => $asset->serial_number ?? 'N/A',
            'mac_address' => $asset->mac_address ?? 'N/A',
            'status' => $asset->current_status,
            'condition' => $asset->condition,
            'lookup_url' => route('assets.qr.lookup', $asset->asset_tag),
        ];
    }
}
