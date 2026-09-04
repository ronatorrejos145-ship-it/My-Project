<?php

namespace Database\Factories;

use App\Models\ServicePackage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServicePackageFactory extends Factory
{
    protected $model = ServicePackage::class;

    public function definition(): array
    {
        return [
            'package_code' => 'PKG-' . $this->faker->unique()->numberBetween(10, 999),
            'name' => 'Fiber Speed ' . $this->faker->numberBetween(25, 500) . ' Mbps',
            'description' => 'High-speed fiber optic internet package.',
            'package_type' => 'RESIDENTIAL',
            'status' => 'ACTIVE',
            'download_speed' => $this->faker->numberBetween(25, 500),
            'upload_speed' => $this->faker->numberBetween(25, 500),
            'speed_unit' => 'Mbps',
            'base_price' => $this->faker->randomElement([999.00, 1299.00, 1499.00, 1999.00, 2499.00]),
            'installation_fee' => 1500.00,
            'activation_fee' => 0.00,
            'deposit_amount' => 0.00,
            'grace_period_days' => 3,
            'contract_period_months' => 24,
        ];
    }
}
