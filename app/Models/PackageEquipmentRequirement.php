<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageEquipmentRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'asset_model_id',
        'quantity',
        'is_required',
        'is_included',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_required' => 'boolean',
        'is_included' => 'boolean',
    ];

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }

    public function assetModel()
    {
        return $this->belongsTo(AssetModel::class, 'asset_model_id');
    }
}
