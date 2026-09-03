<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAuditSession;
use App\Services\AssetQrService;
use App\Services\AssetVerificationService;
use Illuminate\Http\Request;

class AssetQrVerificationController extends Controller
{
    public function lookup(string $assetTag, AssetQrService $qrService)
    {
        $asset = Asset::with(['category', 'model', 'assignedCustomer', 'assignedEmployee'])
            ->where('asset_tag', $assetTag)
            ->firstOrFail();

        $payload = $qrService->generateQrPayload($asset);

        return view('assets.qr.lookup', compact('asset', 'payload'));
    }

    public function verify(Request $request, Asset $asset, AssetVerificationService $verificationService)
    {
        $this->authorize('verify', $asset);

        $validated = $request->validate([
            'physical_presence' => 'required|string|in:FOUND,NOT_FOUND,WRONG_LOCATION,DISCREPANCY',
            'condition' => 'required|string|in:NEW,GOOD,FAIR,POOR,DAMAGED',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $verificationService->recordVerification(
            $asset,
            null,
            $validated['physical_presence'],
            $validated['condition'],
            isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            $validated['notes'] ?? null,
            null,
            auth()->id()
        );

        return back()->with('success', "Asset {$asset->asset_tag} verified on site successfully.");
    }
}
