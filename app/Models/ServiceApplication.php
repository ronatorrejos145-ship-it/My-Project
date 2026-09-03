<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'application_number',
        'customer_id',
        'lead_id',
        'applicant_type',
        'first_name',
        'middle_name',
        'last_name',
        'business_name',
        'primary_phone',
        'secondary_phone',
        'email',
        'service_package_id',
        'service_package_version_id',
        'branch_id',
        'service_area_id',
        'installation_address_id',
        'latitude',
        'longitude',
        'gps_accuracy',
        'location_source',
        'status',
        'application_source',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'rejected_at',
        'cancelled_at',
        'reviewed_by',
        'approved_by',
        'rejected_by',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'gps_accuracy' => 'decimal:2',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function getApplicantNameAttribute(): string
    {
        if ($this->applicant_type === 'BUSINESS' || $this->applicant_type === 'CORPORATE') {
            return $this->business_name ?: trim("{$this->first_name} {$this->last_name}");
        }

        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}") ?: "Application #{$this->application_number}";
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'service_package_id');
    }

    public function packageVersion()
    {
        return $this->belongsTo(ServicePackageVersion::class, 'service_package_version_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function serviceArea()
    {
        return $this->belongsTo(ServiceArea::class);
    }

    public function installationAddress()
    {
        return $this->belongsTo(Address::class, 'installation_address_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(ServiceApplicationStatusHistory::class, 'application_id')->latest();
    }

    public function serviceabilityChecks()
    {
        return $this->hasMany(ServiceabilityCheck::class, 'application_id')->latest();
    }

    public function latestServiceabilityCheck()
    {
        return $this->hasOne(ServiceabilityCheck::class, 'application_id')->latestOfMany();
    }

    public function documents()
    {
        return $this->hasMany(ApplicationDocument::class, 'application_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
