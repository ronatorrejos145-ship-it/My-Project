<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_number',
        'title',
        'description',
        'severity',
        'status',
        'started_at',
        'resolved_at',
        'lead_investigator_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function leadInvestigator()
    {
        return $this->belongsTo(User::class, 'lead_investigator_id');
    }
}
