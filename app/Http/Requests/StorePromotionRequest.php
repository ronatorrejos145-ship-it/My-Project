<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Promotion::class);
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:promotions,code',
            'name' => 'required|string|max:255',
            'promo_type' => 'required|in:DISCOUNT,FREE_INSTALLATION,FIRST_MONTH_FREE,GIFT',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE',
            'packages' => 'nullable|array',
        ];
    }
}
