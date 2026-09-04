<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\ServicePackage;

class PromotionService
{
    /**
     * Check if a promotion is currently active and eligible for a package.
     */
    public function isEligible(Promotion $promo, ServicePackage $package): bool
    {
        if ($promo->status !== 'ACTIVE') {
            return false;
        }

        if ($promo->start_date && $promo->start_date->isFuture()) {
            return false;
        }

        if ($promo->end_date && $promo->end_date->isPast()) {
            return false;
        }

        if ($promo->usage_limit > 0 && $promo->used_count >= $promo->usage_limit) {
            return false;
        }

        if ($promo->packages()->count() > 0) {
            return $promo->packages()->where('service_packages.id', $package->id)->exists();
        }

        return true;
    }
}
