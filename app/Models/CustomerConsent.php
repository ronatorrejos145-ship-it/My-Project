<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerConsent extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'consent_type',
        'version',
        'status',
        'accepted_at',
        'revoked_at',
        'ip_address',
        'user_agent',
        'source',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
