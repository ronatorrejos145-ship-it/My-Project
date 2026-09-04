<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderFailure extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'failure_reason',
        'notes',
        'reported_by_user_id',
        'requires_revisit',
        'rescheduled_date',
    ];

    protected $casts = [
        'requires_revisit' => 'boolean',
        'rescheduled_date' => 'date',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }
}
