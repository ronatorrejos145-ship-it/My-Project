<?php

namespace Database\Factories;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceCategoryFactory extends Factory
{
    protected $model = ServiceCategory::class;

    public function definition(): array
    {
        return [
            'code' => 'CAT-' . strtoupper($this->faker->unique()->lexify('????')),
            'name' => $this->faker->word() . ' Broadband',
            'category_type' => 'HOME',
            'display_order' => $this->faker->numberBetween(1, 10),
            'status' => 'ACTIVE',
        ];
    }
}
