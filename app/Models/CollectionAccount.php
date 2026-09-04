<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'service_account_id',
        'delinquency_status',
        'oldest_unpaid_invoice_date',
        'oldest_unpaid_invoice_number',
        'total_outstanding_amount',
        'overdue_amount',
        'days_overdue',
        'overdue_invoice_count',
        'last_payment_date',
        'last_payment_amount',
        'last_collection_action',
        'next_collection_action_date',
        'suspension_eligibility_date',
        'suspended_at',
        'reconnected_at',
        'assigned_employee_id',
        'risk_level',
        'is_exempt_from_suspension',
        'notes',
    ];

    protected $casts = [
        'oldest_unpaid_invoice_date' => 'date',
        'total_outstanding_amount' => 'decimal:2',
        'overdue_amount' => 'decimal:2',
        'last_payment_date' => 'date',
        'last_payment_amount' => 'decimal:2',
        'next_collection_action_date' => 'date',
        'suspension_eligibility_date' => 'date',
        'suspended_at' => 'datetime',
        'reconnected_at' => 'datetime',
        'is_exempt_from_suspension' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    public function actions()
    {
        return $this->hasMany(CollectionAction::class, 'collection_account_id');
    }

    public function contacts()
    {
        return $this->hasMany(CollectionContact::class, 'collection_account_id');
    }

    public function promises()
    {
        return $this->hasMany(PromiseToPay::class, 'collection_account_id');
    }
}
