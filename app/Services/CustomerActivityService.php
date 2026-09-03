<?php

namespace App\Services;

use App\Models\CustomerActivity;
use Illuminate\Support\Facades\Auth;

class CustomerActivityService
{
    /**
     * Record a new activity event in a customer's Customer 360 timeline.
     */
    public function log(int $customerId, string $activityType, string $title, ?string $description = null, array $metadata = []): CustomerActivity
    {
        return CustomerActivity::create([
            'customer_id' => $customerId,
            'activity_type' => $activityType,
            'title' => $title,
            'description' => $description,
            'metadata' => $metadata,
            'performed_by' => Auth::id(),
            'recorded_at' => now(),
        ]);
    }
}
