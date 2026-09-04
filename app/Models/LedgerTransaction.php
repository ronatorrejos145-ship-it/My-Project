<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'customer_id',
        'service_account_id',
        'invoice_id',
        'transaction_type',
        'transaction_date',
        'posting_date',
        'debit_amount',
        'credit_amount',
        'net_amount',
        'currency',
        'reference_type',
        'reference_id',
        'description',
        'status',
        'reversal_of_id',
        'metadata',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'posting_date' => 'datetime',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'metadata' => 'array',
        'posted_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function reversalOf()
    {
        return $this->belongsTo(LedgerTransaction::class, 'reversal_of_id');
    }
}
