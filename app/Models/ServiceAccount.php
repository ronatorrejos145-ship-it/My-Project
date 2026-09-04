<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_number',
        'customer_id',
        'branch_id',
        'service_type',
        'status',
        'activated_at',
        'terminated_at',
        'primary_location_id',
        'service_username',
        'circuit_id',
        'notes',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'terminated_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function primaryLocation()
    {
        return $this->belongsTo(Location::class, 'primary_location_id');
    }

    public function locations()
    {
        return $this->hasMany(ServiceLocation::class, 'service_account_id');
    }

    public function currentSubscription()
    {
        return $this->hasOne(Subscription::class, 'service_account_id')->latestOfMany();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'service_account_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'service_account_id');
    }

    public function contracts()
    {
        return $this->hasMany(ServiceContract::class, 'service_account_id');
    }
}
