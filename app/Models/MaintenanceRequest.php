<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_number',
        'customer_id',
        'subscription_id',
        'requested_by_user_id',
        'source',
        'title',
        'description',
        'priority',
        'status',
        'preferred_date',
        'preferred_time_slot',
        'approval_notes',
        'work_order_id',
    ];

    protected $casts = [
        'preferred_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
