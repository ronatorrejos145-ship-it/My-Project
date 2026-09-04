<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'previous_latitude',
        'previous_longitude',
        'new_latitude',
        'new_longitude',
        'reason',
        'changed_by',
        'recorded_at',
    ];

    protected $casts = [
        'previous_latitude' => 'decimal:7',
        'previous_longitude' => 'decimal:7',
        'new_latitude' => 'decimal:7',
        'new_longitude' => 'decimal:7',
        'recorded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
