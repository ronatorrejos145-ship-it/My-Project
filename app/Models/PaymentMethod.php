<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'method_type',
        'provider',
        'requires_reference',
        'status',
    ];

    protected $casts = [
        'requires_reference' => 'boolean',
    ];
}
