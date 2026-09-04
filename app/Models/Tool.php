<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tool extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tool_code',
        'category_id',
        'name',
        'manufacturer',
        'model',
        'serial_number',
        'condition',
        'status',
        'purchase_date',
        'location',
        'assigned_employee_id',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(ToolCategory::class, 'category_id');
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }
}
