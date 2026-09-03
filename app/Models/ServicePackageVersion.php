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
        'version_name',
        'effective_from',
        'effective_until',
        'price',
        'installation_fee',
        'activation_fee',
        'deposit_amount',
        'reconnection_fee',
        'relocation_fee',
        'equipment_fee',
        'download_speed',
        'upload_speed',
        'guaranteed_speed',
        'speed_unit',
        'billing_cycle_id',
        'contract_period_months',
        'grace_period_days',
        'status',
        'change_reason',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
        'price' => 'decimal:2',
        'installation_fee' => 'decimal:2',
        'activation_fee' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'reconnection_fee' => 'decimal:2',
        'relocation_fee' => 'decimal:2',
        'equipment_fee' => 'decimal:2',
        'download_speed' => 'integer',
        'upload_speed' => 'integer',
        'guaranteed_speed' => 'integer',
        'contract_period_months' => 'integer',
        'grace_period_days' => 'integer',
        'version_number' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }

    public function billingCycle()
    {
        return $this->belongsTo(BillingCycle::class, 'billing_cycle_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
