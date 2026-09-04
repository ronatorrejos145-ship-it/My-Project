<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'address_type',
        'address_line_1',
        'address_line_2',
        'house_number',
        'building',
        'unit',
        'street',
        'subdivision',
        'purok',
        'sitio',
        'barangay_id',
        'city_municipality_id',
        'province_id',
        'region_id',
        'postal_code',
        'country',
        'landmark',
        'latitude',
        'longitude',
        'coordinate_accuracy',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'coordinate_accuracy' => 'decimal:2',
    ];

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function cityMunicipality()
    {
        return $this->belongsTo(CityMunicipality::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
