<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillableEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'customer_id',
        'service_account_id',
        'subscription_id',
        'event_date',
        'effective_date',
        'source_module',
        'source_id',
        'quantity',
        'unit_price',
        'calculated_amount',
        'metadata',
        'status',
        'processed_at',
        'idempotency_key',
    ];

    protected $casts = [
        'event_date' => 'date',
        'effective_date' => 'date',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'calculated_amount' => 'decimal:2',
        'metadata' => 'array',
        'processed_at' => 'datetime',
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
}
