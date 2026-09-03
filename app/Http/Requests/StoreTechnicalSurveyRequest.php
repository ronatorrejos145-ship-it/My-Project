<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTechnicalSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\TechnicalSurvey::class);
    }

    public function rules(): array
    {
        return [
            'application_id' => 'required|exists:service_applications,id',
            'technician_id' => 'nullable|exists:employees,id',
            'priority' => 'required|in:LOW,MEDIUM,HIGH,URGENT',
            'survey_type' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ];
    }
}
