<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicalSurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'checklist_item_id',
        'response_value',
        'pass_flag',
        'notes',
    ];

    protected $casts = [
        'pass_flag' => 'boolean',
    ];

    public function survey()
    {
        return $this->belongsTo(TechnicalSurvey::class, 'survey_id');
    }

    public function checklistItem()
    {
        return $this->belongsTo(TechnicalSurveyChecklistItem::class, 'checklist_item_id');
    }
}
