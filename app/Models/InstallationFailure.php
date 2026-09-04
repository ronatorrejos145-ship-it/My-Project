<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationFailure extends Model
{
    use HasFactory;

    protected $fillable = [
        'installation_id',
        'failure_category',
        'detailed_reason',
        'failed_by',
        'failed_at',
        'recommended_action',
        'notes',
    ];

    protected $casts = [
        'failed_at' => 'datetime',
    ];

    public function installation()
    {
        return $this->belongsTo(InstallationWorkOrder::class, 'installation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'failed_by');
    }
}
