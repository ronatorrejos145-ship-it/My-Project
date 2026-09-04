<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignTechnicalSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assign', $this->route('survey'));
    }

    public function rules(): array
    {
        return [
            'technician_id' => 'required|exists:employees,id',
            'scheduled_at' => 'nullable|date',
            'priority' => 'nullable|in:LOW,MEDIUM,HIGH,URGENT',
            'notes' => 'nullable|string',
        ];
    }
}
