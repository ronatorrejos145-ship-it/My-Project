<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'customer_id',
        'service_account_id',
        'payment_reference',
        'payment_date',
        'amount',
        'currency',
        'payment_method',
        'reference_number',
        'status',
        'generated_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'generated_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class, 'receipt_id');
    }
}
