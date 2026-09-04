<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Customer::class);
    }

    public function rules(): array
    {
        return [
            'customer_type' => 'required|in:INDIVIDUAL,RESIDENTIAL,FAMILY,BUSINESS,CORPORATE,SCHOOL,GOVERNMENT,ORGANIZATION,OTHER',
            'first_name' => 'required_if:customer_type,INDIVIDUAL,RESIDENTIAL,FAMILY|nullable|string|max:100',
            'last_name' => 'required_if:customer_type,INDIVIDUAL,RESIDENTIAL,FAMILY|nullable|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'suffix' => 'nullable|string|max:20',
            'business_name' => 'required_if:customer_type,BUSINESS,CORPORATE,SCHOOL,GOVERNMENT,ORGANIZATION|nullable|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'primary_phone' => 'required|string|max:50',
            'secondary_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'occupation' => 'nullable|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'assigned_employee_id' => 'nullable|exists:employees,id',
            'acquisition_source' => 'required|string|max:50',
            'status' => 'required|in:PROSPECT,LEAD,APPLICANT,PENDING_VERIFICATION,VERIFIED,APPROVED,ACTIVE,TEMPORARILY_INACTIVE,SUSPENDED,TERMINATION_PENDING,TERMINATED,BLACKLISTED,ARCHIVED',
            'installation_address' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ];
    }
}
