<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'activity_type',
        'title',
        'description',
        'metadata',
        'performed_by',
        'recorded_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'recorded_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
