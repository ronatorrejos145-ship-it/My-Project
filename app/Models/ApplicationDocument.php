<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'application_id',
        'document_type',
        'original_filename',
        'storage_path',
        'mime_type',
        'file_size',
        'checksum',
        'verification_status',
        'uploaded_by',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'verified_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(ServiceApplication::class, 'application_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
