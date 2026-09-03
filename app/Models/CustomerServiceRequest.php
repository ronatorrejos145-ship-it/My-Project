<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'customer_id',
        'service_account_id',
        'request_type',
        'target_package_id',
        'payload',
        'status',
        'admin_notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    public function targetPackage()
    {
        return $this->belongsTo(ServicePackage::class, 'target_package_id');
    }
}
