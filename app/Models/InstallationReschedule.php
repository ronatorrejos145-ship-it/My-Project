<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationReschedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'installation_id',
        'previous_scheduled_start',
        'previous_scheduled_end',
        'new_scheduled_start',
        'new_scheduled_end',
        'reason',
        'rescheduled_by',
    ];

    protected $casts = [
        'previous_scheduled_start' => 'datetime',
        'previous_scheduled_end' => 'datetime',
        'new_scheduled_start' => 'datetime',
        'new_scheduled_end' => 'datetime',
    ];

    public function installation()
    {
        return $this->belongsTo(InstallationWorkOrder::class, 'installation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'rescheduled_by');
    }
}
