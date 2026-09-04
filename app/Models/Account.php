<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'parent_account_id',
        'normal_balance',
        'status',
        'description',
    ];

    public function parentAccount()
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    public function childAccounts()
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }
}
