<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'run_number',
        'billing_date',
        'period_start',
        'period_end',
        'billing_cycle',
        'status',
        'total_accounts',
        'eligible_accounts',
        'skipped_accounts',
        'successful_accounts',
        'failed_accounts',
        'total_charges',
        'total_amount',
        'started_at',
        'completed_at',
        'initiated_by',
        'error_summary',
    ];

    protected $casts = [
        'billing_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'total_amount' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function charges()
    {
        return $this->hasMany(BillableCharge::class, 'billing_run_id');
    }

    public function exceptions()
    {
        return $this->hasMany(BillingException::class, 'billing_run_id');
    }
}
