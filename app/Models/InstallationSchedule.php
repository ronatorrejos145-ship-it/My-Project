<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'installation_id',
        'scheduled_date',
        'start_time',
        'end_time',
        'technician_id',
        'team',
        'appointment_status',
        'is_override',
        'override_reason',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'is_override' => 'boolean',
    ];

    public function installation()
    {
        return $this->belongsTo(InstallationWorkOrder::class, 'installation_id');
    }

    public function technician()
    {
        return $this->belongsTo(Employee::class, 'technician_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
