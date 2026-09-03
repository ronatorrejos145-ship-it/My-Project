<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subscription_number',
        'service_account_id',
        'customer_id',
        'package_id',
        'package_version_id',
        'installation_id',
        'package_name_snapshot',
        'download_speed_snapshot',
        'upload_speed_snapshot',
        'monthly_price_snapshot',
        'billing_cycle_snapshot',
        'contract_duration_months',
        'status',
        'start_date',
        'end_date',
        'next_billing_date',
        'notes',
    ];

    protected $casts = [
        'monthly_price_snapshot' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'next_billing_date' => 'datetime',
    ];

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }

    public function packageVersion()
    {
        return $this->belongsTo(ServicePackageVersion::class, 'package_version_id');
    }

    public function installation()
    {
        return $this->belongsTo(InstallationWorkOrder::class, 'installation_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(SubscriptionStatusHistory::class, 'subscription_id');
    }
}
