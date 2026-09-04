<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TechnicalSurveyPhoto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'survey_id',
        'category',
        'original_filename',
        'storage_path',
        'mime_type',
        'file_size',
        'latitude',
        'longitude',
        'caption',
        'uploaded_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'file_size' => 'integer',
    ];

    public function survey()
    {
        return $this->belongsTo(TechnicalSurvey::class, 'survey_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
