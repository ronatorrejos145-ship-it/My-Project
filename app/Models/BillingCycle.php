<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'interval',
        'interval_unit',
        'description',
        'status',
    ];

    public function packages()
    {
        return $this->hasMany(ServicePackage::class, 'billing_cycle_id');
    }
}
