<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'employee_id',
        'activity_type',
        'scheduled_at',
        'completed_at',
        'outcome',
        'notes',
        'next_follow_up_at',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
