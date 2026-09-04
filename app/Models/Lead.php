<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_number',
        'converted_customer_id',
        'name',
        'company',
        'mobile',
        'telephone',
        'email',
        'source',
        'campaign',
        'referral_customer_id',
        'assigned_employee_id',
        'branch_id',
        'address_id',
        'latitude',
        'longitude',
        'interested_package_id',
        'priority',
        'status',
        'expected_conversion_date',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'expected_conversion_date' => 'date',
    ];

    public function convertedCustomer()
    {
        return $this->belongsTo(Customer::class, 'converted_customer_id');
    }

    public function referralCustomer()
    {
        return $this->belongsTo(Customer::class, 'referral_customer_id');
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function interestedPackage()
    {
        return $this->belongsTo(ServicePackage::class, 'interested_package_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(LeadStatusHistory::class);
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }
}
