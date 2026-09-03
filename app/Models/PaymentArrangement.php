<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentArrangement extends Model
{
    use HasFactory;

    protected $fillable = [
        'arrangement_number',
        'customer_id',
        'service_account_id',
        'total_amount',
        'down_payment_amount',
        'installment_amount',
        'installment_frequency',
        'total_installments',
        'paid_installments',
        'start_date',
        'due_day_of_month',
        'remaining_balance',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'down_payment_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'start_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function serviceAccount()
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    public function installments()
    {
        return $this->hasMany(PaymentArrangementInstallment::class, 'arrangement_id')->orderBy('installment_number', 'asc');
    }
}
