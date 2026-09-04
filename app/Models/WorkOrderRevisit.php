<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderRevisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_work_order_id',
        'follow_up_work_order_id',
        'reason',
    ];

    public function originalWorkOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'original_work_order_id');
    }

    public function followUpWorkOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'follow_up_work_order_id');
    }
}
