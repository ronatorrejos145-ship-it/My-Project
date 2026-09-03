<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'document_type',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'uploaded_by',
        'checksum',
        'notes',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
