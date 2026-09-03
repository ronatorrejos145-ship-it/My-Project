<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\ServiceCategory::class);
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:service_categories,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_type' => 'required|string|max:50',
            'display_order' => 'nullable|integer',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }
}
