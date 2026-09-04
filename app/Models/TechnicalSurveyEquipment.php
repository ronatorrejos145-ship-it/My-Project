<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicalSurveyEquipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'asset_model_id',
        'quantity',
        'is_required',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_required' => 'boolean',
    ];

    public function survey()
    {
        return $this->belongsTo(TechnicalSurvey::class, 'survey_id');
    }

    public function assetModel()
    {
        return $this->belongsTo(AssetModel::class, 'asset_model_id');
    }
}
