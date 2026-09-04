<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderCustomerConfirmation extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'customer_id',
        'confirmed_by_name',
        'signature_file_path',
        'rating',
        'customer_comments',
        'confirmed_at',
        'ip_address',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
