<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'promo_type',
        'discount_amount',
        'discount_percentage',
        'start_date',
        'end_date',
        'usage_limit',
        'used_count',
        'customer_usage_limit',
        'public_visibility',
        'stackable_flag',
        'status',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:4',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'customer_usage_limit' => 'integer',
        'public_visibility' => 'boolean',
        'stackable_flag' => 'boolean',
    ];

    public function packages()
    {
        return $this->belongsToMany(ServicePackage::class, 'promotion_service_package')->withTimestamps();
    }
}
