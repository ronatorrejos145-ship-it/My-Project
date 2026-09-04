<?php

namespace Database\Factories;

use App\Models\ServiceabilityCheck;
use App\Models\ServicePackage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceabilityCheckFactory extends Factory
{
    protected $model = ServiceabilityCheck::class;

    public function definition(): array
    {
        return [
            'package_id' => ServicePackage::factory(),
            'latitude' => 14.6507000,
            'longitude' => 121.0300000,
            'result_status' => 'SERVICEABLE',
            'reason_code' => 'FIBER_NODE_IN_RANGE',
            'explanation' => 'Location is within range of Fiber POP.',
            'calculated_distance_meters' => 250.00,
            'capacity_status' => 'CAPACITY_AVAILABLE',
            'checked_at' => now(),
            'engine_version' => '1.0.0',
        ];
    }
}
