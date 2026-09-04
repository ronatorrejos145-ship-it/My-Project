<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_number',
        'requester_id',
        'branch_id',
        'warehouse_id',
        'priority',
        'status',
        'required_date',
        'estimated_total',
        'justification',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'required_date' => 'date',
        'approved_at' => 'datetime',
        'estimated_total' => 'decimal:2',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class, 'purchase_request_id');
    }
}
