<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispute_number',
        'customer_id',
        'invoice_id',
        'payment_id',
        'dispute_type',
        'disputed_amount',
        'description',
        'status',
        'resolution_summary',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'disputed_amount' => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
