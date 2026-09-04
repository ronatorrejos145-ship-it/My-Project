<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicalSurveyAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'previous_technician_id',
        'new_technician_id',
        'assigned_by',
        'notes',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function survey()
    {
        return $this->belongsTo(TechnicalSurvey::class, 'survey_id');
    }

    public function newTechnician()
    {
        return $this->belongsTo(Employee::class, 'new_technician_id');
    }
}
