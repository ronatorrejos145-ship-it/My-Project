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
        'service_category_id',
        'name',
        'short_name',
        'description',
        'package_type',
        'technology',
        'status',
        'download_speed',
        'upload_speed',
        'speed_guaranteed',
        'burst_speed',
        'speed_unit',
        'base_price',
        'installation_fee',
        'activation_fee',
        'deposit_amount',
        'reconnection_fee',
        'relocation_fee',
        'billing_cycle_id',
        'tax_id',
        'grace_period_days',
        'contract_period_months',
        'fair_use_policy',
        'fup_enabled',
        'fup_threshold_gb',
        'fup_action',
        'data_allowance_gb',
        'public_visibility',
        'featured',
        'display_order',
        'terms',
        'notes',
        'approval_status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'installation_fee' => 'decimal:2',
        'activation_fee' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'reconnection_fee' => 'decimal:2',
        'relocation_fee' => 'decimal:2',
        'download_speed' => 'integer',
        'upload_speed' => 'integer',
        'speed_guaranteed' => 'integer',
        'burst_speed' => 'integer',
        'grace_period_days' => 'integer',
        'contract_period_months' => 'integer',
        'fup_enabled' => 'boolean',
        'fup_threshold_gb' => 'integer',
        'data_allowance_gb' => 'integer',
        'public_visibility' => 'boolean',
        'featured' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function getDownloadSpeedFormattedAttribute(): string
    {
        return "{$this->download_speed} {$this->speed_unit}";
    }

    public function getUploadSpeedFormattedAttribute(): string
    {
        return "{$this->upload_speed} {$this->speed_unit}";
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

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
        return $this->hasMany(ServicePackageVersion::class, 'package_id')->orderBy('version_number', 'desc');
    }

    public function activeVersion()
    {
        return $this->hasOne(ServicePackageVersion::class, 'package_id')->where('status', 'ACTIVE')->latestOfMany('version_number');
    }

    public function features()
    {
        return $this->belongsToMany(PackageFeature::class, 'package_feature_service_package')
            ->withPivot('feature_value')
            ->withTimestamps();
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'service_package_branch')->withTimestamps();
    }

    public function serviceAreas()
    {
        return $this->belongsToMany(ServiceArea::class, 'service_package_service_area')->withTimestamps();
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_service_package')->withTimestamps();
    }

    public function equipmentRequirements()
    {
        return $this->hasMany(PackageEquipmentRequirement::class, 'package_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
