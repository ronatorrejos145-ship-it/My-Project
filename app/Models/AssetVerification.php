<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'audit_session_id',
        'verified_by',
        'verified_at',
        'latitude',
        'longitude',
        'physical_presence',
        'condition',
        'notes',
        'photo_path',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function auditSession()
    {
        return $this->belongsTo(AssetAuditSession::class, 'audit_session_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
