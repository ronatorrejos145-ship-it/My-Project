<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetDisposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'disposal_number',
        'disposal_method',
        'disposed_by',
        'authorized_by',
        'disposed_at',
        'sale_price',
        'certificate_number',
        'notes',
    ];

    protected $casts = [
        'disposed_at' => 'datetime',
        'sale_price' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
