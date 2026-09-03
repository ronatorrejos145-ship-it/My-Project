<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitTechnicalSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('submit', $this->route('survey'));
    }

    public function rules(): array
    {
        return [
            'line_of_sight_status' => 'required|in:CLEAR,PARTIAL,BLOCKED,UNKNOWN,NOT_APPLICABLE',
            'line_of_sight_notes' => 'nullable|string',
            'installation_complexity' => 'required|in:EASY,NORMAL,MODERATE,DIFFICULT,VERY_DIFFICULT',
            'safety_assessment' => 'required|in:SAFE,CAUTION,UNSAFE',
            'technical_summary' => 'required|string',
            'measurements' => 'nullable|array',
            'materials' => 'nullable|array',
            'equipment' => 'nullable|array',
        ];
    }
}
