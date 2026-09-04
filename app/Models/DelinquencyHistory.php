<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelinquencyHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'service_account_id',
        'previous_status',
        'new_status',
        'reason',
        'changed_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
