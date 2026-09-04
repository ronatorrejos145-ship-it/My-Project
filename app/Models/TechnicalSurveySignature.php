<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicalSurveySignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'signer_type',
        'signer_name',
        'signature_data',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function survey()
    {
        return $this->belongsTo(TechnicalSurvey::class, 'survey_id');
    }
}
