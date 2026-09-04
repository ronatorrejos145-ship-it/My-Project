<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianAvailability extends Model
{
    use HasFactory;

    protected $table = 'technician_availability';

    protected $fillable = [
        'technician_id',
        'working_days',
        'work_start_time',
        'work_end_time',
        'is_on_leave',
        'current_status',
        'current_latitude',
        'current_longitude',
        'last_gps_at',
    ];

    protected $casts = [
        'working_days' => 'array',
        'is_on_leave' => 'boolean',
        'last_gps_at' => 'datetime',
    ];

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
