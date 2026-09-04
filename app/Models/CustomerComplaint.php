<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerComplaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_number',
        'customer_id',
        'ticket_id',
        'category',
        'severity',
        'description',
        'root_cause_analysis',
        'action_taken',
        'status',
        'assigned_officer_id',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function assignedOfficer()
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }
}
