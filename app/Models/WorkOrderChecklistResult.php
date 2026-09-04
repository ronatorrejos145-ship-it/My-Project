<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderChecklistResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'template_id',
        'checklist_item_id',
        'result_value',
        'is_passed',
        'notes',
        'completed_by_user_id',
        'completed_at',
    ];

    protected $casts = [
        'is_passed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function template()
    {
        return $this->belongsTo(WorkOrderChecklistTemplate::class, 'template_id');
    }

    public function item()
    {
        return $this->belongsTo(WorkOrderChecklistItem::class, 'checklist_item_id');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }
}
