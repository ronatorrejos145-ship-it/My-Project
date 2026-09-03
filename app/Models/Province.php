<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $fillable = ['region_id', 'code', 'name'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function citiesMunicipalities()
    {
        return $this->hasMany(CityMunicipality::class);
    }
}
