<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketPriority extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'level', 'color_code'];

    protected $casts = [
        'level' => 'integer',
    ];
}
