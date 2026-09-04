<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationAcceptance extends Model
{
    use HasFactory;

    protected $fillable = [
        'installation_id',
        'customer_id',
        'signer_name',
        'signer_relationship',
        'acceptance_status',
        'rejection_reason',
        'signature_path',
        'signed_at',
        'ip_address',
        'user_agent',
        'notes',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function installation()
    {
        return $this->belongsTo(InstallationWorkOrder::class, 'installation_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
