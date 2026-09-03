<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_number',
        'service_account_id',
        'customer_id',
        'subscription_id',
        'start_date',
        'end_date',
        'monthly_fee',
        'deposit_amount',
        'status',
        'contract_file_path',
        'terms',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_fee' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }
}
