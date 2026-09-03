<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReconnectionExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'reconnection_request_id',
        'subscription_id',
        'action_type',
        'provider',
        'status',
        'response_payload',
        'error_message',
        'executed_at',
    ];

    protected $casts = [
        'response_payload' => 'array',
        'executed_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(ReconnectionRequest::class, 'reconnection_request_id');
    }
}
