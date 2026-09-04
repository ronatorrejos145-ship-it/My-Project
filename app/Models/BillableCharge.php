<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillableCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'charge_number',
        'billing_run_id',
        'billing_period_id',
        'billing_profile_id',
        'customer_id',
        'service_account_id',
        'subscription_id',
        'package_id',
        'package_version_id',
        'charge_type',
        'source_type',
        'source_id',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
        'discount_amount',
        'taxable_amount',
        'tax_amount',
        'total_amount',
        'currency',
        'service_period_start',
        'service_period_end',
        'effective_date',
        'status',
        'idempotency_key',
        'calculation_snapshot',
        'metadata',
        'generated_at',
        'finalized_at',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'service_period_start' => 'date',
        'service_period_end' => 'date',
        'effective_date' => 'date',
        'calculation_snapshot' => 'array',
        'metadata' => 'array',
        'generated_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function billingProfile()
    {
        return $this->belongsTo(BillingProfile::class, 'billing_profile_id');
    }

    public function billingPeriod()
    {
        return $this->belongsTo(BillingPeriod::class, 'billing_period_id');
    }

    public function billingRun()
    {
        return $this->belongsTo(BillingRun::class, 'billing_run_id');
    }
}
