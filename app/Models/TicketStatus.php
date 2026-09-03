<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'is_closed'];

    protected $casts = [
        'is_closed' => 'boolean',
    ];
}
