<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_number',
        'account_number',
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'legal_name',
        'business_name',
        'trade_name',
        'date_of_birth',
        'occupation',
        'customer_type',
        'status',
        'contact_person',
        'primary_phone',
        'secondary_phone',
        'email',
        'installation_address',
        'billing_address',
        'branch_id',
        'assigned_employee_id',
        'referred_by_customer_id',
        'acquisition_source',
        'primary_address_id',
        'installation_address_id',
        'billing_address_id',
        'current_balance',
        'credit_limit',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'current_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
    ];

    public function getFullNameAttribute(): string
    {
        if ($this->customer_type === 'BUSINESS' || $this->customer_type === 'CORPORATE') {
            return $this->business_name ?: $this->legal_name ?: $this->contact_person ?: "Customer #{$this->customer_number}";
        }

        return trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}") ?: $this->contact_person ?: "Customer #{$this->customer_number}";
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    public function referredBy()
    {
        return $this->belongsTo(Customer::class, 'referred_by_customer_id');
    }

    public function primaryAddress()
    {
        return $this->belongsTo(Address::class, 'primary_address_id');
    }

    public function installationAddress()
    {
        return $this->belongsTo(Address::class, 'installation_address_id');
    }

    public function billingAddress()
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function contacts()
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function documents()
    {
        return $this->hasMany(CustomerDocument::class);
    }

    public function notes()
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function tags()
    {
        return $this->belongsToMany(CustomerTag::class, 'customer_customer_tag')->withTimestamps();
    }

    public function statusHistories()
    {
        return $this->hasMany(CustomerStatusHistory::class);
    }

    public function activities()
    {
        return $this->hasMany(CustomerActivity::class)->latest('recorded_at');
    }

    public function assignments()
    {
        return $this->hasMany(CustomerAssignment::class)->latest();
    }

    public function consents()
    {
        return $this->hasMany(CustomerConsent::class);
    }

    public function referrals()
    {
        return $this->hasMany(CustomerReferral::class, 'referrer_customer_id');
    }
}
