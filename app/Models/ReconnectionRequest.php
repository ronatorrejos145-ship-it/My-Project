<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReconnectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'customer_id',
        'service_account_id',
        'subscription_id',
        'suspension_request_id',
        'payment_id',
        'amount_paid',
        'amount_remaining',
        'reconnection_fee',
        'reconnection_fee_waived',
        'waived_by',
        'approval_status',
        'network_action_status',
        'requested_by',
        'approved_by',
        'approved_at',
        'executed_at',
        'notes',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'amount_remaining' => 'decimal:2',
        'reconnection_fee' => 'decimal:2',
        'reconnection_fee_waived' => 'boolean',
        'approved_at' => 'datetime',
        'executed_at' => 'datetime',
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

    public function executions()
    {
        return $this->hasMany(ReconnectionExecution::class, 'reconnection_request_id');
    }
}
