<?php

namespace Database\Factories;

use App\Models\ServiceApplication;
use App\Models\ServicePackage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceApplicationFactory extends Factory
{
    protected $model = ServiceApplication::class;

    public function definition(): array
    {
        return [
            'application_number' => 'APP-2025-' . $this->faker->unique()->numberBetween(100000, 999999),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'primary_phone' => '+63 9' . $this->faker->numerify('#########'),
            'email' => $this->faker->unique()->safeEmail(),
            'service_package_id' => ServicePackage::factory(),
            'latitude' => 14.6507000,
            'longitude' => 121.0300000,
            'status' => 'SUBMITTED',
            'application_source' => 'ONLINE_PORTAL',
            'submitted_at' => now(),
        ];
    }
}
