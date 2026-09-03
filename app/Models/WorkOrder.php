<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_number',
        'work_order_type',
        'customer_id',
        'service_area_id',
        'address_id',
        'latitude',
        'longitude',
        'priority',
        'status',
        'assigned_employee_id',
        'supervisor_id',
        'scheduled_at',
        'completed_at',
        'description',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceArea()
    {
        return $this->belongsTo(ServiceArea::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }
}
