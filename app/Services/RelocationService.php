<?php

namespace App\Services;

use App\Models\ServiceAccount;
use App\Models\ServiceLocation;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;

class RelocationService
{
    public function executeRelocation(
        ServiceAccount $serviceAccount,
        ?int $newAddressId,
        ?int $newLocationId,
        ?float $latitude,
        ?float $longitude,
        ?string $notes = null,
        ?int $userId = null
    ): ServiceLocation {
        return DB::transaction(function () use ($serviceAccount, $newAddressId, $newLocationId, $latitude, $longitude, $notes, $userId) {
            $serviceAccount = ServiceAccount::where('id', $serviceAccount->id)->lockForUpdate()->firstOrFail();

            // Mark previous active service location as non-current
            ServiceLocation::where('service_account_id', $serviceAccount->id)
                ->where('is_current', true)
                ->update(['is_current' => false, 'effective_to' => now()->toDateString()]);

            // Create new active service location
            $newServiceLocation = ServiceLocation::create([
                'service_account_id' => $serviceAccount->id,
                'address_id' => $newAddressId,
                'location_id' => $newLocationId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'is_current' => true,
                'effective_from' => now()->toDateString(),
            ]);

            if ($newLocationId) {
                $serviceAccount->update(['primary_location_id' => $newLocationId]);
            }

            AuditLogService::log(
                'EXECUTE_RELOCATION',
                'services',
                $serviceAccount,
                null,
                ['new_location_id' => $newLocationId, 'latitude' => $latitude, 'longitude' => $longitude]
            );

            return $newServiceLocation;
        });
    }
}
