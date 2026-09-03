<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Lead::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'mobile' => 'required|string|max:50',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'source' => 'required|string|max:50',
            'campaign' => 'nullable|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'assigned_employee_id' => 'nullable|exists:employees,id',
            'interested_package_id' => 'nullable|exists:service_packages,id',
            'priority' => 'required|in:LOW,MEDIUM,HIGH,URGENT',
            'status' => 'required|in:NEW,CONTACTED,QUALIFIED,UNQUALIFIED,FOLLOW_UP,CONVERTED,LOST,CLOSED',
            'expected_conversion_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }
}
