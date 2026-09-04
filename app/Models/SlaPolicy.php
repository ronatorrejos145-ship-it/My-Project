<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'priority',
        'first_response_minutes',
        'resolution_minutes',
        'business_hours_only',
        'is_active',
    ];

    protected $casts = [
        'business_hours_only' => 'boolean',
        'is_active' => 'boolean',
    ];
}
