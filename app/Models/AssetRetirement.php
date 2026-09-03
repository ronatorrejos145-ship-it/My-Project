<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetRetirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'retired_by',
        'retired_at',
        'reason',
        'residual_value',
        'notes',
    ];

    protected $casts = [
        'retired_at' => 'datetime',
        'residual_value' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
