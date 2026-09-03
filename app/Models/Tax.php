<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'rate',
        'tax_type',
        'is_inclusive',
        'effective_from',
        'effective_until',
        'status',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_inclusive' => 'boolean',
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];
}
