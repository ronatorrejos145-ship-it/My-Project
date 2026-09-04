<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'installation_id',
        'old_status',
        'new_status',
        'changed_by',
        'reason',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function installation()
    {
        return $this->belongsTo(InstallationWorkOrder::class, 'installation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
