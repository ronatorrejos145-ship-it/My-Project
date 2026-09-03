<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadCustomerDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('upload', \App\Models\CustomerDocument::class);
    }

    public function rules(): array
    {
        return [
            'document_type' => 'required|in:VALID_ID,PROOF_OF_ADDRESS,BUSINESS_REG,AUTHORIZATION_LETTER,CONTRACT,APPLICATION,OTHER',
            'document_number' => 'nullable|string|max:100',
            'expiration_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB limit
        ];
    }
}
