<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'technician_id',
        'assigned_by_user_id',
        'team_name',
        'is_primary',
        'status',
        'notes',
        'assigned_at',
        'accepted_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'assigned_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }
}
