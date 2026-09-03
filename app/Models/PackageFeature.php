<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageFeature extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'description'];

    public function packages()
    {
        return $this->belongsToMany(ServicePackage::class, 'package_feature_service_package')
            ->withPivot('feature_value')
            ->withTimestamps();
    }
}
