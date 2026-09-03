<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromiseToPay extends Model
{
    use HasFactory;

    protected $table = 'promises_to_pay';

    protected $fillable = [
        'promise_number',
        'customer_id',
        'service_account_id',
        'collection_account_id',
        'promised_amount',
        'promised_date',
        'status',
        'fulfilled_amount',
        'fulfilled_at',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'promised_amount' => 'decimal:2',
        'promised_date' => 'date',
        'fulfilled_amount' => 'decimal:2',
        'fulfilled_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
