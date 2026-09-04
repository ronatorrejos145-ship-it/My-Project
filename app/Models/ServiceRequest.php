<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'service_account_id',
        'customer_id',
        'request_type',
        'priority',
        'status',
        'request_payload',
        'reason',
        'approved_by',
        'approved_at',
        'completed_by',
        'completed_at',
        'admin_notes',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
