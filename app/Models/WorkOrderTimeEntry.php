<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderTimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'technician_id',
        'entry_type',
        'start_at',
        'end_at',
        'duration_minutes',
        'notes',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
