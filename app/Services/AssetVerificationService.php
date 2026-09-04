<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetAuditSession;
use App\Models\AssetVerification;
use Illuminate\Support\Facades\DB;

class AssetVerificationService
{
    public function recordVerification(
        Asset $asset,
        ?AssetAuditSession $session = null,
        string $physicalPresence = 'FOUND',
        string $condition = 'GOOD',
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $notes = null,
        ?string $photoPath = null,
        ?int $userId = null
    ): AssetVerification {
        return DB::transaction(function () use ($asset, $session, $physicalPresence, $condition, $latitude, $longitude, $notes, $photoPath, $userId) {
            $verification = AssetVerification::create([
                'asset_id' => $asset->id,
                'audit_session_id' => $session?->id,
                'verified_by' => $userId,
                'verified_at' => now(),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'physical_presence' => $physicalPresence,
                'condition' => $condition,
                'notes' => $notes,
                'photo_path' => $photoPath,
            ]);

            if ($condition !== $asset->condition) {
                $asset->update(['condition' => $condition]);
            }

            if ($session) {
                $session->increment('verified_count');
                if ($physicalPresence !== 'FOUND') {
                    $session->increment('discrepancy_count');
                }
            }

            AuditLogService::log(
                'VERIFY_ASSET',
                'assets',
                $verification,
                null,
                $verification->toArray()
            );

            return $verification;
        });
    }
}
