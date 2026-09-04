<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'customer_type',
        'min_overdue_amount',
        'min_days_overdue',
        'grace_period_days',
        'suspension_threshold_days',
        'is_active',
    ];

    protected $casts = [
        'min_overdue_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function steps()
    {
        return $this->hasMany(CollectionPolicyStep::class, 'policy_id')->orderBy('step_number', 'asc');
    }
}
