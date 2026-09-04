<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'installation_id',
        'test_type',
        'measured_value',
        'unit',
        'result',
        'threshold_applied',
        'test_source',
        'device_used',
        'performed_by',
        'performed_at',
        'notes',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    public function installation()
    {
        return $this->belongsTo(InstallationWorkOrder::class, 'installation_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
