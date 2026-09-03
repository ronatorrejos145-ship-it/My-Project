<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetInterface extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'interface_name',
        'interface_type',
        'mac_address',
        'ip_address',
        'vlan',
        'speed_mbps',
        'status',
        'description',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
