<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderDowntime extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'customer_id',
        'subscription_id',
        'outage_start_at',
        'outage_end_at',
        'duration_minutes',
        'is_service_restored',
        'restoration_verified_by_user_id',
        'notes',
    ];

    protected $casts = [
        'outage_start_at' => 'datetime',
        'outage_end_at' => 'datetime',
        'is_service_restored' => 'boolean',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function restorationVerifiedBy()
    {
        return $this->belongsTo(User::class, 'restoration_verified_by_user_id');
    }
}
