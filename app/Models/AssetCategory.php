<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['code', 'name', 'description', 'status'];

    public function assetModels()
    {
        return $this->hasMany(AssetModel::class, 'category_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'asset_category_id');
    }
}
