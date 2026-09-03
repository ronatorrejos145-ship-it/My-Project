<?php

namespace Database\Factories;

use App\Models\TechnicalSurveyMeasurement;
use App\Models\TechnicalSurvey;
use Illuminate\Database\Eloquent\Factories\Factory;

class TechnicalSurveyMeasurementFactory extends Factory
{
    protected $model = TechnicalSurveyMeasurement::class;

    public function definition(): array
    {
        return [
            'survey_id' => TechnicalSurvey::factory(),
            'measurement_type' => 'OPTICAL_POWER',
            'value' => -18.50,
            'unit' => 'dBm',
            'acceptance_status' => 'PASS',
            'measurement_tool' => 'Optical Power Meter',
            'measured_at' => now(),
        ];
    }
}
