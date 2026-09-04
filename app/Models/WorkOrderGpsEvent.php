<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderGpsEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'technician_id',
        'event_type',
        'latitude',
        'longitude',
        'location_accuracy',
        'device_info',
        'is_flagged_suspicious',
    ];

    protected $casts = [
        'is_flagged_suspicious' => 'boolean',
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
