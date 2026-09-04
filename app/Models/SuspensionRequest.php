<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuspensionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'customer_id',
        'service_account_id',
        'subscription_id',
        'reason',
        'delinquency_amount',
        'days_overdue',
        'approval_status',
        'network_action_status',
        'requested_by',
        'approved_by',
        'approved_at',
        'executed_at',
        'result_notes',
    ];

    protected $casts = [
        'delinquency_amount' => 'decimal:2',
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
        return $this->hasMany(SuspensionExecution::class, 'suspension_request_id');
    }
}
