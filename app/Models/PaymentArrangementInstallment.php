<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentArrangementInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'arrangement_id',
        'installment_number',
        'due_date',
        'amount_due',
        'amount_paid',
        'status',
        'paid_at',
        'invoice_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function arrangement()
    {
        return $this->belongsTo(PaymentArrangement::class, 'arrangement_id');
    }
}
