<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NetworkTower extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'tower_type',
        'height_meters',
        'owner',
        'latitude',
        'longitude',
        'service_area_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'height_meters' => 'decimal:2',
    ];

    public function serviceArea()
    {
        return $this->belongsTo(ServiceArea::class);
    }
}
