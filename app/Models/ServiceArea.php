<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceArea extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'branch_id',
        'description',
        'boundary_geojson',
        'color_code',
        'geometry_version',
        'status',
        'serviceability_status',
        'notes',
    ];

    protected $casts = [
        'boundary_geojson' => 'array',
        'geometry_version' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function networkNodes()
    {
        return $this->hasMany(NetworkNode::class);
    }

    public function networkTowers()
    {
        return $this->hasMany(NetworkTower::class);
    }

    public function barangays()
    {
        return $this->belongsToMany(Barangay::class, 'service_area_geographic_area');
    }
}
