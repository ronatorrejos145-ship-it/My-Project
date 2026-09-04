<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_id',
        'invoice_id',
        'allocated_amount',
        'allocated_at',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'allocated_at' => 'datetime',
    ];

    public function receipt()
    {
        return $this->belongsTo(Receipt::class, 'receipt_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
