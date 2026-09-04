<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'incident_type',
        'reported_by',
        'incident_date',
        'last_known_location',
        'reference_number',
        'detailed_description',
        'status',
        'notes',
    ];

    protected $casts = [
        'incident_date' => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
