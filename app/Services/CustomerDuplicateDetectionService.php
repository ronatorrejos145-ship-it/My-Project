<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerDuplicateDetectionService
{
    /**
     * Check for potential duplicate customers using mobile, email, name, or company.
     */
    public function findDuplicates(array $data, ?int $excludeCustomerId = null): Collection
    {
        $query = Customer::query();

        if ($excludeCustomerId) {
            $query->where('id', '!=', $excludeCustomerId);
        }

        $query->where(function ($q) use ($data) {
            if (!empty($data['primary_phone'])) {
                $q->orWhere('primary_phone', $data['primary_phone']);
            }
            if (!empty($data['secondary_phone'])) {
                $q->orWhere('secondary_phone', $data['secondary_phone']);
            }
            if (!empty($data['email'])) {
                $q->orWhere('email', $data['email']);
            }
            if (!empty($data['legal_name'])) {
                $q->orWhere('legal_name', 'like', "%{$data['legal_name']}%");
            }
            if (!empty($data['business_name'])) {
                $q->orWhere('business_name', 'like', "%{$data['business_name']}%");
            }
            if (!empty($data['first_name']) && !empty($data['last_name'])) {
                $q->orWhere(function ($sub) use ($data) {
                    $sub->where('first_name', $data['first_name'])
                        ->where('last_name', $data['last_name']);
                });
            }
        });

        return $query->take(5)->get();
    }
}
