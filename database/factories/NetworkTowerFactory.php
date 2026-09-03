<?php

namespace Database\Factories;

use App\Models\NetworkTower;
use Illuminate\Database\Eloquent\Factories\Factory;

class NetworkTowerFactory extends Factory
{
    protected $model = NetworkTower::class;

    public function definition(): array
    {
        return [
            'code' => 'TWR-' . strtoupper($this->faker->unique()->lexify('????')),
            'name' => $this->faker->city() . ' Telecom Tower',
            'tower_type' => 'ROOFTOP',
            'height_meters' => 30.00,
            'owner' => 'COMPANY',
            'latitude' => 14.6507000,
            'longitude' => 121.0300000,
            'status' => 'ACTIVE',
        ];
    }
}
