<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'default_debit_account_id',
        'default_credit_account_id',
        'description',
    ];

    public function defaultDebitAccount()
    {
        return $this->belongsTo(Account::class, 'default_debit_account_id');
    }

    public function defaultCreditAccount()
    {
        return $this->belongsTo(Account::class, 'default_credit_account_id');
    }
}
