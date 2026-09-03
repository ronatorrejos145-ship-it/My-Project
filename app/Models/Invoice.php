<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'service_account_id',
        'billing_period_id',
        'billing_run_id',
        'invoice_date',
        'period_start',
        'period_end',
        'due_date',
        'grace_date',
        'currency',
        'subtotal',
        'discount_amount',
        'taxable_amount',
        'tax_amount',
        'total_amount',
        'amount_paid',
        'amount_due',
        'status',
        'notes',
        'terms',
        'finalized_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'grace_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'finalized_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    public function billingPeriod()
    {
        return $this->belongsTo(BillingPeriod::class, 'billing_period_id');
    }

    public function lines()
    {
        return $this->hasMany(InvoiceLine::class, 'invoice_id');
    }

    public function ledgerTransactions()
    {
        return $this->hasMany(LedgerTransaction::class, 'invoice_id');
    }
}
