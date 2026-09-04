<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServicePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('package'));
    }

    public function rules(): array
    {
        return [
            'package_code' => 'required|string|max:50|unique:service_packages,package_code,' . $this->route('package')->id,
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'package_type' => 'required|in:RESIDENTIAL,BUSINESS,CORPORATE,PREPAID,POSTPAID,DEDICATED,PUBLIC_WIFI,CUSTOM',
            'technology' => 'required|in:FIBER,WIRELESS,FIXED_WIRELESS,FTTH,FTTB,RADIO,MESH,HOTSPOT,HYBRID,OTHER',
            'description' => 'nullable|string',
            'terms' => 'nullable|string',
            'status' => 'required|in:DRAFT,ACTIVE,INACTIVE,DISCONTINUED,ARCHIVED',
            'features' => 'nullable|array',
            'branches' => 'nullable|array',
            'service_areas' => 'nullable|array',
        ];
    }
}
