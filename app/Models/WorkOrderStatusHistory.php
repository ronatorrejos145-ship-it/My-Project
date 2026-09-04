<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'old_status',
        'new_status',
        'changed_by_user_id',
        'reason',
        'notes',
        'ip_address',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
