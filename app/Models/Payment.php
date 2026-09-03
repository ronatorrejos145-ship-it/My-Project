<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_number',
        'customer_id',
        'service_account_id',
        'payment_method_id',
        'payment_method_code',
        'payment_channel',
        'amount',
        'currency',
        'payment_date',
        'received_at',
        'reference_number',
        'external_reference',
        'gateway_transaction_id',
        'gateway_provider',
        'status',
        'verification_status',
        'verified_by',
        'verified_at',
        'failure_reason',
        'notes',
        'metadata',
        'idempotency_key',
        'created_by',
        'posted_at',
        'reversed_at',
        'reversed_by',
        'reversal_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'received_at' => 'datetime',
        'verified_at' => 'datetime',
        'posted_at' => 'datetime',
        'reversed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function proofs()
    {
        return $this->hasMany(PaymentProof::class, 'payment_id');
    }

    public function verifications()
    {
        return $this->hasMany(PaymentVerification::class, 'payment_id');
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class, 'payment_id');
    }
}
