<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'previous_employee_id',
        'new_employee_id',
        'previous_branch_id',
        'new_branch_id',
        'effective_date',
        'reason',
        'changed_by',
    ];

    protected $casts = [
        'effective_date' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function previousEmployee()
    {
        return $this->belongsTo(Employee::class, 'previous_employee_id');
    }

    public function newEmployee()
    {
        return $this->belongsTo(Employee::class, 'new_employee_id');
    }
}
