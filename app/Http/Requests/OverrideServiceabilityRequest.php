<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OverrideServiceabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('override', $this->route('check'));
    }

    public function rules(): array
    {
        return [
            'override_status' => 'required|in:SERVICEABLE,REQUIRES_TECHNICAL_SURVEY,OUT_OF_COVERAGE',
            'override_reason' => 'required|string|max:500',
        ];
    }
}
