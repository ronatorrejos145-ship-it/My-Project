<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccessPoint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'node_id',
        'network_device_id',
        'code',
        'name',
        'technology',
        'frequency',
        'ssid',
        'coverage_notes',
        'latitude',
        'longitude',
        'status',
        'capacity',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'capacity' => 'integer',
    ];

    public function node()
    {
        return $this->belongsTo(NetworkNode::class, 'node_id');
    }

    public function device()
    {
        return $this->belongsTo(NetworkDevice::class, 'network_device_id');
    }
}
