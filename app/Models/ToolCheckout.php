<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolCheckout extends Model
{
    use HasFactory;

    protected $fillable = [
        'checkout_number',
        'tool_id',
        'employee_id',
        'issued_at',
        'expected_return_at',
        'returned_at',
        'condition_on_issue',
        'condition_on_return',
        'issued_by',
        'received_by',
        'notes',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expected_return_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function tool()
    {
        return $this->belongsTo(Tool::class, 'tool_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
