<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'installation_id',
        'previous_technician_id',
        'new_technician_id',
        'previous_team',
        'new_team',
        'assigned_by',
        'assignment_reason',
    ];

    public function installation()
    {
        return $this->belongsTo(InstallationWorkOrder::class, 'installation_id');
    }

    public function previousTechnician()
    {
        return $this->belongsTo(Employee::class, 'previous_technician_id');
    }

    public function newTechnician()
    {
        return $this->belongsTo(Employee::class, 'new_technician_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
