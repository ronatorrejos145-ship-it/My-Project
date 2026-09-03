<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePackageVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'version_number',
        'effective_from',
        'effective_until',
        'price',
        'installation_fee',
        'activation_fee',
        'deposit_amount',
        'download_speed',
        'upload_speed',
        'speed_unit',
        'billing_cycle_id',
        'status',
        'change_reason',
    ];

    protected $casts = [
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
        'price' => 'decimal:2',
        'installation_fee' => 'decimal:2',
        'activation_fee' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'download_speed' => 'integer',
        'upload_speed' => 'integer',
        'version_number' => 'integer',
    ];

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }

    public function billingCycle()
    {
        return $this->belongsTo(BillingCycle::class, 'billing_cycle_id');
    }
}
