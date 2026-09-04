<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'branch_id',
        'manager_employee_id',
        'address',
        'status',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_employee_id');
    }

    public function locations()
    {
        return $this->hasMany(WarehouseLocation::class);
    }
}
