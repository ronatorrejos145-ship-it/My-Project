<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TechnicalSurvey extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'survey_number',
        'application_id',
        'customer_id',
        'package_id',
        'package_version_id',
        'technician_id',
        'supervisor_id',
        'survey_type',
        'status',
        'priority',
        'scheduled_at',
        'started_at',
        'completed_at',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'rejected_at',
        'resurvey_requested_at',
        'arrival_latitude',
        'arrival_longitude',
        'arrival_gps_accuracy',
        'arrival_verification_status',
        'arrival_distance_meters',
        'line_of_sight_status',
        'line_of_sight_notes',
        'installation_complexity',
        'safety_assessment',
        'technical_recommendation',
        'final_decision',
        'technical_summary',
        'rejection_reason',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'arrival_latitude' => 'decimal:7',
        'arrival_longitude' => 'decimal:7',
        'arrival_gps_accuracy' => 'decimal:2',
        'arrival_distance_meters' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'resurvey_requested_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(ServiceApplication::class, 'application_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }

    public function packageVersion()
    {
        return $this->belongsTo(ServicePackageVersion::class, 'package_version_id');
    }

    public function technician()
    {
        return $this->belongsTo(Employee::class, 'technician_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(TechnicalSurveyStatusHistory::class, 'survey_id')->latest();
    }

    public function assignments()
    {
        return $this->hasMany(TechnicalSurveyAssignment::class, 'survey_id')->latest();
    }

    public function responses()
    {
        return $this->hasMany(TechnicalSurveyResponse::class, 'survey_id');
    }

    public function measurements()
    {
        return $this->hasMany(TechnicalSurveyMeasurement::class, 'survey_id');
    }

    public function photos()
    {
        return $this->hasMany(TechnicalSurveyPhoto::class, 'survey_id');
    }

    public function materials()
    {
        return $this->hasMany(TechnicalSurveyMaterial::class, 'survey_id');
    }

    public function equipment()
    {
        return $this->hasMany(TechnicalSurveyEquipment::class, 'survey_id');
    }

    public function signatures()
    {
        return $this->hasMany(TechnicalSurveySignature::class, 'survey_id');
    }
}
