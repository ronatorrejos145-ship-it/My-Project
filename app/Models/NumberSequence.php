<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumberSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'prefix',
        'suffix',
        'current_number',
        'padding',
        'reset_period',
        'last_reset_date',
        'is_branch_aware',
        'status',
    ];

    protected $casts = [
        'current_number' => 'integer',
        'padding' => 'integer',
        'is_branch_aware' => 'boolean',
    ];
}
