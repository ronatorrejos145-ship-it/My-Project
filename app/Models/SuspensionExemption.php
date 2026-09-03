<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuspensionExemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'service_account_id',
        'reason',
        'start_date',
        'expiry_date',
        'status',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
