<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NetworkNode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'node_code',
        'name',
        'node_type',
        'status',
        'branch_id',
        'service_area_id',
        'parent_node_id',
        'location_id',
        'latitude',
        'longitude',
        'coordinate_accuracy',
        'address',
        'description',
        'installation_date',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'coordinate_accuracy' => 'decimal:2',
        'installation_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function serviceArea()
    {
        return $this->belongsTo(ServiceArea::class);
    }

    public function parentNode()
    {
        return $this->belongsTo(NetworkNode::class, 'parent_node_id');
    }

    public function childNodes()
    {
        return $this->hasMany(NetworkNode::class, 'parent_node_id');
    }

    public function accessPoints()
    {
        return $this->hasMany(AccessPoint::class, 'node_id');
    }

    public function networkDevices()
    {
        return $this->hasMany(NetworkDevice::class, 'node_id');
    }
}
