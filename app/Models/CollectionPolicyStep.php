<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionPolicyStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_id',
        'step_number',
        'trigger_days_overdue',
        'action_type',
        'template_name',
        'is_automatic',
    ];

    protected $casts = [
        'is_automatic' => 'boolean',
    ];

    public function policy()
    {
        return $this->belongsTo(CollectionPolicy::class, 'policy_id');
    }
}
