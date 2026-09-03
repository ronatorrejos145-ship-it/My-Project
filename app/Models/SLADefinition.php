<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SLADefinition extends Model
{
    use HasFactory;

    protected $table = 'sla_definitions';

    protected $fillable = [
        'name',
        'category_id',
        'priority_id',
        'response_time_minutes',
        'resolution_time_minutes',
        'business_hours',
        'escalation_level',
        'status',
    ];

    protected $casts = [
        'response_time_minutes' => 'integer',
        'resolution_time_minutes' => 'integer',
        'escalation_level' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function priority()
    {
        return $this->belongsTo(TicketPriority::class, 'priority_id');
    }
}
