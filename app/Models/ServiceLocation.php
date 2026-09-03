<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_account_id',
        'address_id',
        'location_id',
        'service_area_id',
        'latitude',
        'longitude',
        'landmark',
        'is_current',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_current' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function serviceArea()
    {
        return $this->belongsTo(ServiceArea::class, 'service_area_id');
    }
}
