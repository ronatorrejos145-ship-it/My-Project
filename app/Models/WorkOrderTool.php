<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderTool extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'tool_id',
        'technician_id',
        'checked_out_at',
        'checked_in_at',
        'condition_before',
        'condition_after',
        'is_damaged',
        'damage_notes',
    ];

    protected $casts = [
        'is_damaged' => 'boolean',
        'checked_out_at' => 'datetime',
        'checked_in_at' => 'datetime',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
