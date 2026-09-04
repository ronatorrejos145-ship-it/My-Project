<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();

        return [
            'customer_number' => 'CUST-' . $this->faker->unique()->numberBetween(100000, 999999),
            'account_number' => 'ACC-' . $this->faker->unique()->numberBetween(100000, 999999),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'contact_person' => "{$firstName} {$lastName}",
            'customer_type' => 'RESIDENTIAL',
            'status' => $this->faker->randomElement(['PROSPECT', 'VERIFIED', 'ACTIVE', 'SUSPENDED']),
            'primary_phone' => '+63 9' . $this->faker->numerify('#########'),
            'email' => $this->faker->unique()->safeEmail(),
            'acquisition_source' => 'WEBSITE',
            'current_balance' => 0.00,
            'credit_limit' => 2000.00,
        ];
    }
}
