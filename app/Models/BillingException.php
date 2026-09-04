<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingException extends Model
{
    use HasFactory;

    protected $fillable = [
        'exception_number',
        'billing_run_id',
        'service_account_id',
        'subscription_id',
        'severity',
        'type',
        'message',
        'details',
        'status',
        'resolved_by',
        'resolved_at',
        'resolution_note',
    ];

    protected $casts = [
        'details' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }
}
