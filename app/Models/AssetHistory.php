<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'previous_location',
        'new_location',
        'previous_status',
        'new_status',
        'previous_employee_id',
        'new_employee_id',
        'previous_customer_id',
        'new_customer_id',
        'action',
        'reason',
        'performed_by',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
