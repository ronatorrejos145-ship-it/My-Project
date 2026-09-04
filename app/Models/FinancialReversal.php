<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialReversal extends Model
{
    use HasFactory;

    protected $fillable = [
        'reversal_number',
        'original_transaction_id',
        'reversal_transaction_id',
        'reason',
        'amount',
        'reversed_by',
        'reversed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'reversed_at' => 'datetime',
    ];

    public function originalTransaction()
    {
        return $this->belongsTo(LedgerTransaction::class, 'original_transaction_id');
    }

    public function reversalTransaction()
    {
        return $this->belongsTo(LedgerTransaction::class, 'reversal_transaction_id');
    }

    public function reversedBy()
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }
}
