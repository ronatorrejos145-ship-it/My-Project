<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNetworkTowerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\NetworkTower::class);
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:network_towers,code',
            'name' => 'required|string|max:255',
            'tower_type' => 'required|in:ROOFTOP,MONOPOLE,LATTICE,GUYED,OTHER',
            'height_meters' => 'required|numeric|min:0',
            'owner' => 'required|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'service_area_id' => 'nullable|exists:service_areas,id',
            'status' => 'required|in:ACTIVE,INACTIVE,MAINTENANCE,PLANNED,DECOMMISSIONED',
            'notes' => 'nullable|string',
        ];
    }
}
