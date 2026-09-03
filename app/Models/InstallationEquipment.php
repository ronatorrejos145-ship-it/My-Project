<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationEquipment extends Model
{
    use HasFactory;

    protected $table = 'installation_equipment';

    protected $fillable = [
        'installation_id',
        'asset_id',
        'equipment_type',
        'model_name',
        'serial_number',
        'mac_address',
        'condition_before',
        'condition_after',
        'assigned_by',
        'assigned_at',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function installation()
    {
        return $this->belongsTo(InstallationWorkOrder::class, 'installation_id');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
