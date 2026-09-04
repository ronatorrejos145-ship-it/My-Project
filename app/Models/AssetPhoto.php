<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'photo_category',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'latitude',
        'longitude',
        'uploaded_by',
        'checksum',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
