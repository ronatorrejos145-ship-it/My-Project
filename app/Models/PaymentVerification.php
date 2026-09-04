<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'verifier_id',
        'status',
        'amount_confirmed',
        'reference_confirmed',
        'notes',
        'verified_at',
    ];

    protected $casts = [
        'amount_confirmed' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }
}
