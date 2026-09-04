<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationHandoff extends Model
{
    use HasFactory;

    protected $fillable = [
        'installation_id',
        'customer_id',
        'application_id',
        'technical_survey_id',
        'package_id',
        'package_version_id',
        'location_id',
        'latitude',
        'longitude',
        'status',
        'handover_data',
        'handoff_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'handover_data' => 'array',
        'handoff_at' => 'datetime',
    ];

    public function installation()
    {
        return $this->belongsTo(InstallationWorkOrder::class, 'installation_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function application()
    {
        return $this->belongsTo(ServiceApplication::class, 'application_id');
    }

    public function technicalSurvey()
    {
        return $this->belongsTo(TechnicalSurvey::class, 'technical_survey_id');
    }

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }

    public function packageVersion()
    {
        return $this->belongsTo(ServicePackageVersion::class, 'package_version_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
