<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NetworkDevice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'device_code',
        'device_name',
        'device_type',
        'hostname',
        'management_ip',
        'mac_address',
        'serial_number',
        'manufacturer',
        'model',
        'firmware_version',
        'node_id',
        'parent_device_id',
        'location_id',
        'latitude',
        'longitude',
        'capacity',
        'status',
        'installation_date',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'installation_date' => 'date',
        'capacity' => 'integer',
    ];

    public function node()
    {
        return $this->belongsTo(NetworkNode::class, 'node_id');
    }

    public function parentDevice()
    {
        return $this->belongsTo(NetworkDevice::class, 'parent_device_id');
    }

    public function childDevices()
    {
        return $this->hasMany(NetworkDevice::class, 'parent_device_id');
    }

    public function interfaces()
    {
        return $this->hasMany(NetworkInterface::class, 'device_id');
    }
}
