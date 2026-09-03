<?php

namespace Database\Factories;

use App\Models\TechnicalSurvey;
use App\Models\ServicePackage;
use Illuminate\Database\Eloquent\Factories\Factory;

class TechnicalSurveyFactory extends Factory
{
    protected $model = TechnicalSurvey::class;

    public function definition(): array
    {
        return [
            'survey_number' => 'SUR-2025-' . $this->faker->unique()->numberBetween(100000, 999999),
            'package_id' => ServicePackage::factory(),
            'survey_type' => 'NEW_INSTALLATION',
            'status' => 'PENDING_TECHNICAL_REVIEW',
            'priority' => 'MEDIUM',
            'scheduled_at' => now(),
            'arrival_latitude' => 14.6520000,
            'arrival_longitude' => 121.0320000,
            'arrival_verification_status' => 'ARRIVED_AT_SITE',
            'arrival_distance_meters' => 25.00,
            'line_of_sight_status' => 'CLEAR',
            'installation_complexity' => 'NORMAL',
            'safety_assessment' => 'SAFE',
            'technical_recommendation' => 'RECOMMENDED',
            'final_decision' => 'TECHNICALLY_FEASIBLE',
        ];
    }
}
