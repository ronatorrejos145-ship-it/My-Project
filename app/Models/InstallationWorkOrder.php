<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallationWorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_number',
        'application_id',
        'customer_id',
        'technical_survey_id',
        'package_id',
        'package_version_id',
        'branch_id',
        'service_area_id',
        'installation_address_id',
        'installation_location_id',
        'latitude',
        'longitude',
        'gps_accuracy',
        'work_type',
        'priority',
        'source',
        'reason',
        'requested_date',
        'target_date',
        'scheduled_start',
        'scheduled_end',
        'assigned_team',
        'assigned_technician_id',
        'supervisor_id',
        'status',
        'customer_appointment_status',
        'customer_confirmation_at',
        'started_at',
        'arrived_at',
        'testing_started_at',
        'acceptance_requested_at',
        'accepted_at',
        'completed_at',
        'failed_at',
        'cancelled_at',
        'failure_reason',
        'cancellation_reason',
        'reschedule_reason',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'gps_accuracy' => 'decimal:2',
        'requested_date' => 'date',
        'target_date' => 'date',
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'customer_confirmation_at' => 'datetime',
        'started_at' => 'datetime',
        'arrived_at' => 'datetime',
        'testing_started_at' => 'datetime',
        'acceptance_requested_at' => 'datetime',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(ServiceApplication::class, 'application_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
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

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function serviceArea()
    {
        return $this->belongsTo(ServiceArea::class, 'service_area_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'installation_address_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'installation_location_id');
    }

    public function assignedTechnician()
    {
        return $this->belongsTo(Employee::class, 'assigned_technician_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusHistories()
    {
        return $this->hasMany(InstallationStatusHistory::class, 'installation_id');
    }

    public function assignments()
    {
        return $this->hasMany(InstallationAssignment::class, 'installation_id');
    }

    public function schedules()
    {
        return $this->hasMany(InstallationSchedule::class, 'installation_id');
    }

    public function checklistResponses()
    {
        return $this->hasMany(InstallationChecklistResponse::class, 'installation_id');
    }

    public function photos()
    {
        return $this->hasMany(InstallationPhoto::class, 'installation_id');
    }

    public function materials()
    {
        return $this->hasMany(InstallationMaterial::class, 'installation_id');
    }

    public function equipment()
    {
        return $this->hasMany(InstallationEquipment::class, 'installation_id');
    }

    public function tools()
    {
        return $this->hasMany(InstallationTool::class, 'installation_id');
    }

    public function tests()
    {
        return $this->hasMany(InstallationTest::class, 'installation_id');
    }

    public function failures()
    {
        return $this->hasMany(InstallationFailure::class, 'installation_id');
    }

    public function reschedules()
    {
        return $this->hasMany(InstallationReschedule::class, 'installation_id');
    }

    public function acceptances()
    {
        return $this->hasMany(InstallationAcceptance::class, 'installation_id');
    }

    public function supervisorReviews()
    {
        return $this->hasMany(InstallationSupervisorReview::class, 'installation_id');
    }

    public function installationNotes()
    {
        return $this->hasMany(InstallationNote::class, 'installation_id');
    }

    public function handoff()
    {
        return $this->hasOne(InstallationHandoff::class, 'installation_id');
    }
}
