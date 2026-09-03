<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_profile_id',
        'period_start',
        'period_end',
        'billing_date',
        'due_date',
        'grace_date',
        'status',
        'generated_at',
        'finalized_at',
        'billing_run_id',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'billing_date' => 'date',
        'due_date' => 'date',
        'grace_date' => 'date',
        'generated_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function billingProfile()
    {
        return $this->belongsTo(BillingProfile::class, 'billing_profile_id');
    }

    public function charges()
    {
        return $this->hasMany(BillableCharge::class, 'billing_period_id');
    }
}
