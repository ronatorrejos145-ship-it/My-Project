<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'document_type',
        'document_number',
        'original_filename',
        'storage_path',
        'mime_type',
        'file_size',
        'checksum',
        'uploaded_by',
        'uploaded_at',
        'expiration_date',
        'verification_status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_at' => 'datetime',
        'expiration_date' => 'date',
        'verified_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
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
