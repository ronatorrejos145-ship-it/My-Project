<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;

    protected $fillable = ['city_municipality_id', 'code', 'name', 'district'];

    public function cityMunicipality()
    {
        return $this->belongsTo(CityMunicipality::class);
    }
}
