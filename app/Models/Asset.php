<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_tag',
        'asset_category_id',
        'asset_model_id',
        'serial_number',
        'mac_address',
        'manufacturer',
        'purchase_date',
        'purchase_cost',
        'warranty_start',
        'warranty_end',
        'current_status',
        'current_location',
        'assigned_employee_id',
        'assigned_customer_id',
        'network_device_id',
        'condition',
        'notes',
    ];

    protected $casts = [
        'purchase_cost' => 'decimal:2',
        'purchase_date' => 'date',
        'warranty_start' => 'date',
        'warranty_end' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function model()
    {
        return $this->belongsTo(AssetModel::class, 'asset_model_id');
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    public function assignedCustomer()
    {
        return $this->belongsTo(Customer::class, 'assigned_customer_id');
    }

    public function networkDevice()
    {
        return $this->belongsTo(NetworkDevice::class, 'network_device_id');
    }

    public function histories()
    {
        return $this->hasMany(AssetHistory::class, 'asset_id');
    }
}
