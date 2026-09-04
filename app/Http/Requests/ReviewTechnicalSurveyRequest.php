<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewTechnicalSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('review', $this->route('survey'));
    }

    public function rules(): array
    {
        return [
            'decision' => 'required|in:APPROVED,REJECTED,REQUEST_RESURVEY',
            'reason' => 'required_if:decision,REJECTED,REQUEST_RESURVEY|nullable|string|max:500',
            'notes' => 'nullable|string',
        ];
    }
}
