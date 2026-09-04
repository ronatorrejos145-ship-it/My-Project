<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerNotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'email_billing',
        'sms_billing',
        'email_promotions',
        'sms_promotions',
        'email_outages',
        'sms_outages',
    ];

    protected $casts = [
        'email_billing' => 'boolean',
        'sms_billing' => 'boolean',
        'email_promotions' => 'boolean',
        'sms_promotions' => 'boolean',
        'email_outages' => 'boolean',
        'sms_outages' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
