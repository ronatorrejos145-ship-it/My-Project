<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicalSurveyMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'item_id',
        'item_name',
        'estimated_quantity',
        'unit',
        'notes',
    ];

    protected $casts = [
        'estimated_quantity' => 'decimal:2',
    ];

    public function survey()
    {
        return $this->belongsTo(TechnicalSurvey::class, 'survey_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
