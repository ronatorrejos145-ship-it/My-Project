<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\ServicePackage::class);
    }

    public function rules(): array
    {
        return [
            'package_code' => 'required|string|max:50|unique:service_packages,package_code',
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'package_type' => 'required|in:RESIDENTIAL,BUSINESS,CORPORATE,PREPAID,POSTPAID,DEDICATED,PUBLIC_WIFI,CUSTOM',
            'technology' => 'required|in:FIBER,WIRELESS,FIXED_WIRELESS,FTTH,FTTB,RADIO,MESH,HOTSPOT,HYBRID,OTHER',
            'download_speed' => 'required|integer|min:1',
            'upload_speed' => 'required|integer|min:1',
            'speed_unit' => 'required|in:Kbps,Mbps,Gbps',
            'base_price' => 'required|numeric|min:0',
            'installation_fee' => 'nullable|numeric|min:0',
            'activation_fee' => 'nullable|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'reconnection_fee' => 'nullable|numeric|min:0',
            'relocation_fee' => 'nullable|numeric|min:0',
            'billing_cycle_id' => 'nullable|exists:billing_cycles,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'grace_period_days' => 'nullable|integer|min:0',
            'contract_period_months' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'terms' => 'nullable|string',
            'status' => 'required|in:DRAFT,ACTIVE,INACTIVE,DISCONTINUED,ARCHIVED',
            'features' => 'nullable|array',
            'branches' => 'nullable|array',
            'service_areas' => 'nullable|array',
        ];
    }
}
