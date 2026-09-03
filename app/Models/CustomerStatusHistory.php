<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'old_status',
        'new_status',
        'reason',
        'notes',
        'changed_by',
        'source',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
