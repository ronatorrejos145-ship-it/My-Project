<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionPoint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'dp_type',
        'capacity',
        'parent_node_id',
        'latitude',
        'longitude',
        'status',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'capacity' => 'integer',
    ];

    public function parentNode()
    {
        return $this->belongsTo(NetworkNode::class, 'parent_node_id');
    }
}
