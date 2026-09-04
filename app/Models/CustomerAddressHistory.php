<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddressHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'address_id',
        'address_type',
        'effective_from',
        'effective_until',
        'changed_by',
        'reason',
    ];

    protected $casts = [
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
