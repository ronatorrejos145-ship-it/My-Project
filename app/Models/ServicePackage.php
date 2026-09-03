<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicePackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'package_code',
        'name',
        'description',
        'package_type',
        'status',
        'download_speed',
        'upload_speed',
        'speed_unit',
        'base_price',
        'installation_fee',
        'activation_fee',
        'deposit_amount',
        'billing_cycle_id',
        'tax_id',
        'grace_period_days',
        'contract_period_months',
        'fair_use_policy',
        'notes',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'installation_fee' => 'decimal:2',
        'activation_fee' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'download_speed' => 'integer',
        'upload_speed' => 'integer',
        'grace_period_days' => 'integer',
        'contract_period_months' => 'integer',
    ];

    public function billingCycle()
    {
        return $this->belongsTo(BillingCycle::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function versions()
    {
        return $this->hasMany(ServicePackageVersion::class, 'package_id');
    }

    public function features()
    {
        return $this->belongsToMany(PackageFeature::class, 'package_feature_service_package')
            ->withPivot('feature_value')
            ->withTimestamps();
    }
}
