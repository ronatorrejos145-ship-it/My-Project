<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'installation_id',
        'category',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'latitude',
        'longitude',
        'captured_at',
        'uploaded_by',
        'checksum',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'captured_at' => 'datetime',
    ];

    public function installation()
    {
        return $this->belongsTo(InstallationWorkOrder::class, 'installation_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
