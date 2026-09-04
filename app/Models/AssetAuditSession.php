<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAuditSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_number',
        'name',
        'branch_id',
        'warehouse_id',
        'responsible_employee_id',
        'start_date',
        'end_date',
        'status',
        'expected_count',
        'verified_count',
        'discrepancy_count',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function responsibleEmployee()
    {
        return $this->belongsTo(Employee::class, 'responsible_employee_id');
    }

    public function verifications()
    {
        return $this->hasMany(AssetVerification::class, 'audit_session_id');
    }
}
