<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageFeature extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'feature_type', 'default_value', 'description'];

    public function packages()
    {
        return $this->belongsToMany(ServicePackage::class, 'package_feature_service_package')
            ->withPivot('feature_value')
            ->withTimestamps();
    }
}
