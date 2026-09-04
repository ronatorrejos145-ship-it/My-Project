<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'region_number'];

    public function provinces()
    {
        return $this->hasMany(Province::class);
    }
}
