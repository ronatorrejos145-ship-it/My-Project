<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetworkInterface extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'interface_name',
        'interface_type',
        'mac_address',
        'ip_address',
        'vlan',
        'speed',
        'status',
        'description',
    ];

    public function device()
    {
        return $this->belongsTo(NetworkDevice::class, 'device_id');
    }
}
