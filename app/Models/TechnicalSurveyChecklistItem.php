<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicalSurveyChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'section',
        'item_text',
        'item_type',
        'is_required',
        'display_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'display_order' => 'integer',
    ];

    public function template()
    {
        return $this->belongsTo(TechnicalSurveyChecklistTemplate::class, 'template_id');
    }
}
