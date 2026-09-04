<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentWebhookEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'event_id',
        'event_type',
        'payment_id',
        'payload',
        'status',
        'idempotency_key',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
