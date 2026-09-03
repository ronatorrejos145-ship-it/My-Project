<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CityMunicipality extends Model
{
    use HasFactory;

    protected $table = 'cities_municipalities';

    protected $fillable = ['province_id', 'code', 'name', 'type', 'postal_code'];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function barangays()
    {
        return $this->hasMany(Barangay::class);
    }
}
