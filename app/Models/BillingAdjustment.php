<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'adjustment_number',
        'customer_id',
        'service_account_id',
        'billable_charge_id',
        'adjustment_type',
        'amount',
        'reason',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    public function billableCharge()
    {
        return $this->belongsTo(BillableCharge::class, 'billable_charge_id');
    }
}
