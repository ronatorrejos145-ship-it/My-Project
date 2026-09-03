<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('review', $this->route('application'));
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:DRAFT,SUBMITTED,UNDER_REVIEW,SERVICEABILITY_CHECK,REQUIRES_SURVEY,PENDING_DOCUMENTS,APPROVED,REJECTED,CANCELLED',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string',
        ];
    }
}
