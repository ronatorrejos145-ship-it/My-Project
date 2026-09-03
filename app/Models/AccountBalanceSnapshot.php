<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountBalanceSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'service_account_id',
        'total_debits',
        'total_credits',
        'current_balance',
        'last_transaction_id',
        'last_calculated_at',
    ];

    protected $casts = [
        'total_debits' => 'decimal:2',
        'total_credits' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'last_calculated_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }
}
