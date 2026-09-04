<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicalSurveyStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'previous_status',
        'new_status',
        'reason',
        'notes',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function survey()
    {
        return $this->belongsTo(TechnicalSurvey::class, 'survey_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
