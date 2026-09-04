<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition(): array
    {
        return [
            'code' => 'PROMO-' . strtoupper($this->faker->unique()->lexify('????')),
            'name' => $this->faker->words(3, true) . ' Promo',
            'promo_type' => 'DISCOUNT',
            'discount_amount' => 500.00,
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'status' => 'ACTIVE',
        ];
    }
}
