<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPortalProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'preferred_language',
        'theme',
        'two_factor_enabled',
        'last_login_at',
        'last_login_ip',
    ];

    protected $casts = [
        'two_factor_enabled' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
