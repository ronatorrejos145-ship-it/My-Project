<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerReferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_customer_id',
        'referred_customer_id',
        'referral_code',
        'referral_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'referral_date' => 'date',
    ];

    public function referrer()
    {
        return $this->belongsTo(Customer::class, 'referrer_customer_id');
    }

    public function referred()
    {
        return $this->belongsTo(Customer::class, 'referred_customer_id');
    }
}
