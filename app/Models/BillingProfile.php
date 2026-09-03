<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_account_id',
        'billing_method',
        'billing_cycle',
        'billing_day',
        'billing_start_date',
        'next_billing_date',
        'due_days',
        'grace_days',
        'tax_id',
        'currency',
        'status',
        'auto_bill_enabled',
        'billing_hold',
        'billing_hold_reason',
        'billing_hold_until',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'billing_start_date' => 'date',
        'next_billing_date' => 'date',
        'billing_hold_until' => 'date',
        'auto_bill_enabled' => 'boolean',
        'billing_hold' => 'boolean',
    ];

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function periods()
    {
        return $this->hasMany(BillingPeriod::class, 'billing_profile_id');
    }

    public function charges()
    {
        return $this->hasMany(BillableCharge::class, 'billing_profile_id');
    }
}
