<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolCalibration extends Model
{
    use HasFactory;

    protected $fillable = [
        'tool_id',
        'calibration_date',
        'next_calibration_due',
        'provider_name',
        'certificate_number',
        'status',
        'notes',
    ];

    protected $casts = [
        'calibration_date' => 'date',
        'next_calibration_due' => 'date',
    ];

    public function tool()
    {
        return $this->belongsTo(Tool::class, 'tool_id');
    }
}
