<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceabilityCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'customer_id',
        'package_id',
        'package_version_id',
        'latitude',
        'longitude',
        'gps_accuracy',
        'service_area_id',
        'result_status',
        'reason_code',
        'explanation',
        'nearest_node_id',
        'nearest_access_point_id',
        'nearest_nanobox_id',
        'calculated_distance_meters',
        'capacity_status',
        'checked_at',
        'checked_by',
        'engine_version',
        'is_overridden',
        'override_result_status',
        'override_reason',
        'overridden_by',
        'overridden_at',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'gps_accuracy' => 'decimal:2',
        'calculated_distance_meters' => 'decimal:2',
        'checked_at' => 'datetime',
        'is_overridden' => 'boolean',
        'overridden_at' => 'datetime',
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

    public function serviceArea()
    {
        return $this->belongsTo(ServiceArea::class);
    }

    public function nearestNode()
    {
        return $this->belongsTo(NetworkNode::class, 'nearest_node_id');
    }

    public function nearestAccessPoint()
    {
        return $this->belongsTo(AccessPoint::class, 'nearest_access_point_id');
    }

    public function nearestNanobox()
    {
        return $this->belongsTo(NetworkDevice::class, 'nearest_nanobox_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function overrider()
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }
}
