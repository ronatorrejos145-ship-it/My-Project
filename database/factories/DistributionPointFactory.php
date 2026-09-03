<?php

namespace Database\Factories;

use App\Models\DistributionPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

class DistributionPointFactory extends Factory
{
    protected $model = DistributionPoint::class;

    public function definition(): array
    {
        return [
            'code' => 'DP-' . strtoupper($this->faker->unique()->lexify('????')),
            'name' => 'Fiber Splitter ' . $this->faker->numerify('##'),
            'dp_type' => 'FIBER_SPLITTER',
            'capacity' => 16,
            'latitude' => 14.6510000,
            'longitude' => 121.0310000,
            'status' => 'ACTIVE',
        ];
    }
}
