<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow public online applications and authenticated staff
    }

    public function rules(): array
    {
        return [
            'applicant_type' => 'required|in:INDIVIDUAL,RESIDENTIAL,BUSINESS,CORPORATE,GOVERNMENT',
            'first_name' => 'required_if:applicant_type,INDIVIDUAL,RESIDENTIAL|nullable|string|max:100',
            'last_name' => 'required_if:applicant_type,INDIVIDUAL,RESIDENTIAL|nullable|string|max:100',
            'business_name' => 'required_if:applicant_type,BUSINESS,CORPORATE,GOVERNMENT|nullable|string|max:255',
            'primary_phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'service_package_id' => 'required|exists:service_packages,id',
            'installation_address' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'gps_accuracy' => 'nullable|numeric|min:0',
            'location_source' => 'nullable|string|max:50',
            'branch_id' => 'nullable|exists:branches,id',
            'service_area_id' => 'nullable|exists:service_areas,id',
            'customer_id' => 'nullable|exists:customers,id',
            'lead_id' => 'nullable|exists:leads,id',
            'notes' => 'nullable|string',
        ];
    }
}
