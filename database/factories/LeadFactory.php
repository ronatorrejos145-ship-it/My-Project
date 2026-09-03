<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'lead_number' => 'LEAD-' . $this->faker->unique()->numberBetween(100000, 999999),
            'name' => $this->faker->name(),
            'company' => $this->faker->company(),
            'mobile' => '+63 9' . $this->faker->numerify('#########'),
            'email' => $this->faker->unique()->safeEmail(),
            'source' => $this->faker->randomElement(['WEBSITE', 'WALK_IN', 'FACEBOOK', 'FIELD_SALES', 'REFERRAL']),
            'priority' => 'MEDIUM',
            'status' => 'NEW',
            'notes' => 'Interested in high-speed fiber internet.',
        ];
    }
}
