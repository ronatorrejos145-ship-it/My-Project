<?php

namespace App\Services;

use App\Models\TechnicalSurvey;

class TechnicalSurveyEvaluationService
{
    /**
     * Evaluate field survey evidence (checklists, signal measurements, LOS, safety) and return automated recommendation.
     */
    public function evaluateSurvey(TechnicalSurvey $survey): array
    {
        $survey->load(['measurements', 'responses', 'photos']);

        $reasons = [];
        $isFeasible = true;

        // 1. Line of Sight Check
        if ($survey->line_of_sight_status === 'BLOCKED') {
            $isFeasible = false;
            $reasons[] = 'Line of sight to wireless sector AP is completely blocked by physical obstructions.';
        }

        // 2. Safety Assessment Check
        if ($survey->safety_assessment === 'UNSAFE') {
            $isFeasible = false;
            $reasons[] = 'Site inspection determined unsafe working conditions (e.g. electrical hazards, structural instability).';
        }

        // 3. Evaluate Technical Signal Measurements
        foreach ($survey->measurements as $meas) {
            if ($meas->acceptance_status === 'FAIL') {
                $isFeasible = false;
                $reasons[] = "Signal measurement {$meas->measurement_type} failed acceptance threshold: {$meas->value} {$meas->unit}.";
            }
        }

        // 4. Determine Final Recommendation Code
        $recommendation = 'RECOMMENDED';
        $finalDecision = 'TECHNICALLY_FEASIBLE';

        if (!$isFeasible) {
            $recommendation = 'NOT_RECOMMENDED';
            $finalDecision = 'NOT_FEASIBLE';
        } elseif ($survey->installation_complexity === 'VERY_DIFFICULT' || $survey->line_of_sight_status === 'PARTIAL') {
            $recommendation = 'RECOMMENDED_WITH_CONDITIONS';
            $finalDecision = 'FEASIBLE_WITH_CONDITIONS';
        }

        return [
            'recommendation' => $recommendation,
            'final_decision' => $finalDecision,
            'reasons' => $reasons,
            'summary' => implode(' ', $reasons) ?: 'Field site inspection verified line of sight, signal levels, and safe installation access.',
        ];
    }
}
