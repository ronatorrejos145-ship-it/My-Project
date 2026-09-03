<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDistributionPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\DistributionPoint::class);
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:distribution_points,code',
            'name' => 'required|string|max:255',
            'dp_type' => 'required|in:FIBER_SPLITTER,CABINET,DISTRIBUTION_BOX,POLE,JUNCTION',
            'capacity' => 'required|integer|min:1',
            'parent_node_id' => 'nullable|exists:network_nodes,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'status' => 'required|in:ACTIVE,INACTIVE,MAINTENANCE,PLANNED,DECOMMISSIONED',
            'notes' => 'nullable|string',
        ];
    }
}
