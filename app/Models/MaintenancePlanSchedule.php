<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenancePlanSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_plan_id',
        'asset_id',
        'customer_id',
        'subscription_id',
        'last_run_at',
        'next_due_at',
        'grace_days',
        'status',
        'auto_generate_wo',
    ];

    protected $casts = [
        'last_run_at' => 'datetime',
        'next_due_at' => 'datetime',
        'auto_generate_wo' => 'boolean',
    ];

    public function plan()
    {
        return $this->belongsTo(MaintenancePlan::class, 'maintenance_plan_id');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
