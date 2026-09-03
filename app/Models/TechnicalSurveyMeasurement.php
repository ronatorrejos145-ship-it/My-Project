<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicalSurveyMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'measurement_type',
        'value',
        'unit',
        'acceptance_status',
        'measurement_tool',
        'measured_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'measured_at' => 'datetime',
    ];

    public function survey()
    {
        return $this->belongsTo(TechnicalSurvey::class, 'survey_id');
    }
}
