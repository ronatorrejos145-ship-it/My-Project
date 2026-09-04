<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckServiceabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public or staff
    }

    public function rules(): array
    {
        return [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'service_package_id' => 'required|exists:service_packages,id',
            'service_area_id' => 'nullable|exists:service_areas,id',
        ];
    }
}
