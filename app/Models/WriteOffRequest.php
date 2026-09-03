<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WriteOffRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'write_off_number',
        'customer_id',
        'service_account_id',
        'invoice_id',
        'amount',
        'reason',
        'accounting_reference',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'posted_at',
        'ledger_transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
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
}
