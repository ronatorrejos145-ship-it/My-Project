<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianCertification extends Model
{
    use HasFactory;

    protected $fillable = [
        'technician_id',
        'certification_name',
        'certification_number',
        'issuing_authority',
        'issued_at',
        'expires_at',
        'verification_status',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'expires_at' => 'date',
    ];

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
