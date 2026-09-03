<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'code' => 'BR-' . strtoupper($this->faker->unique()->lexify('????')),
            'name' => $this->faker->city() . ' Branch',
            'branch_type' => 'BRANCH',
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'address' => $this->faker->address(),
            'latitude' => $this->faker->latitude(14.0, 15.0),
            'longitude' => $this->faker->longitude(120.0, 122.0),
            'status' => 'ACTIVE',
        ];
    }
}
