<?php

namespace App\Services;

use App\Models\ServiceAccount;
use App\Models\ServicePackage;
use App\Models\ServicePackageVersion;
use App\Models\ServiceRequest;
use App\Models\Subscription;
use App\Models\SubscriptionStatusHistory;
use Illuminate\Support\Facades\DB;

class PackageChangeService
{
    public function executePackageChange(
        Subscription $subscription,
        ServicePackage $newPackage,
        ServicePackageVersion $newPackageVersion,
        string $changeType = 'PACKAGE_UPGRADE', // PACKAGE_UPGRADE or PACKAGE_DOWNGRADE
        ?string $reason = null,
        ?int $userId = null
    ): Subscription {
        return DB::transaction(function () use ($subscription, $newPackage, $newPackageVersion, $changeType, $reason, $userId) {
            $subscription = Subscription::where('id', $subscription->id)->lockForUpdate()->firstOrFail();

            $oldPrice = $subscription->monthly_price_snapshot;
            $oldPackageName = $subscription->package_name_snapshot;

            // Update subscription commercial snapshot to new package version
            $subscription->update([
                'package_id' => $newPackage->id,
                'package_version_id' => $newPackageVersion->id,
                'package_name_snapshot' => $newPackage->name,
                'download_speed_snapshot' => $newPackageVersion->download_speed,
                'upload_speed_snapshot' => $newPackageVersion->upload_speed,
                'monthly_price_snapshot' => $newPackageVersion->monthly_price,
            ]);

            SubscriptionStatusHistory::create([
                'subscription_id' => $subscription->id,
                'old_status' => $subscription->status,
                'new_status' => $subscription->status,
                'changed_by' => $userId,
                'reason' => "{$changeType}: Changed from {$oldPackageName} (PHP {$oldPrice}) to {$newPackage->name} (PHP {$newPackageVersion->monthly_price}). Reason: {$reason}",
            ]);

            AuditLogService::log(
                'PACKAGE_CHANGE',
                'services',
                $subscription,
                ['package' => $oldPackageName, 'price' => $oldPrice],
                ['package' => $newPackage->name, 'price' => $newPackageVersion->monthly_price, 'type' => $changeType]
            );

            return $subscription;
        });
    }
}
