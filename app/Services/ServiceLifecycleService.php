<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionStatusHistory;
use Illuminate\Support\Facades\DB;

class ServiceLifecycleService
{
    public function transitionSubscriptionStatus(Subscription $subscription, string $newStatus, ?string $reason = null, ?int $userId = null): Subscription
    {
        return DB::transaction(function () use ($subscription, $newStatus, $reason, $userId) {
            $subscription = Subscription::where('id', $subscription->id)->lockForUpdate()->firstOrFail();

            $oldStatus = $subscription->status;
            $subscription->update(['status' => $newStatus]);

            if ($subscription->serviceAccount) {
                if ($newStatus === 'TERMINATED') {
                    $subscription->serviceAccount->update([
                        'status' => 'TERMINATED',
                        'terminated_at' => now(),
                    ]);
                } elseif (in_array($newStatus, ['ACTIVE', 'SUSPENDED'])) {
                    $subscription->serviceAccount->update(['status' => $newStatus]);
                }
            }

            SubscriptionStatusHistory::create([
                'subscription_id' => $subscription->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $userId,
                'reason' => $reason ?? 'Status updated',
            ]);

            AuditLogService::log(
                'SUBSCRIPTION_STATUS_CHANGE',
                'services',
                $subscription,
                ['status' => $oldStatus],
                ['status' => $newStatus]
            );

            return $subscription;
        });
    }
}
