<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('createVersion', $this->route('package'));
    }

    public function rules(): array
    {
        return [
            'version_name' => 'required|string|max:100',
            'effective_from' => 'required|date',
            'price' => 'required|numeric|min:0',
            'installation_fee' => 'nullable|numeric|min:0',
            'activation_fee' => 'nullable|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'reconnection_fee' => 'nullable|numeric|min:0',
            'relocation_fee' => 'nullable|numeric|min:0',
            'download_speed' => 'required|integer|min:1',
            'upload_speed' => 'required|integer|min:1',
            'change_reason' => 'required|string|max:500',
        ];
    }
}
