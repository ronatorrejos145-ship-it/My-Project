<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'branch_type',
        'phone',
        'email',
        'address',
        'latitude',
        'longitude',
        'status',
        'manager_employee_id',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_employee_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function serviceAreas()
    {
        return $this->hasMany(ServiceArea::class);
    }

    public function networkNodes()
    {
        return $this->hasMany(NetworkNode::class);
    }
}
