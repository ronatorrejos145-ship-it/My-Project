<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'manufacturer',
        'model_name',
        'model_number',
        'description',
        'specifications',
        'warranty_period_months',
        'status',
    ];

    protected $casts = [
        'specifications' => 'array',
        'warranty_period_months' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'asset_model_id');
    }
}
