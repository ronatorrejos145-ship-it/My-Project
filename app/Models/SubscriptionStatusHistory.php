<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'old_status',
        'new_status',
        'changed_by',
        'reason',
        'notes',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
