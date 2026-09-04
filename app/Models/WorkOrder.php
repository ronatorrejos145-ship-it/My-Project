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
        'maintenance_request_id',
        'ticket_id',
        'complaint_id',
        'incident_id',
        'maintenance_plan_schedule_id',
        'parent_work_order_id',
        'customer_id',
        'subscription_id',
        'asset_id',
        'work_order_type',
        'status',
        'priority',
        'severity',
        'title',
        'description',
        'symptoms',
        'suspected_cause',
        'actual_root_cause',
        'corrective_action',
        'preventive_action',
        'service_address',
        'latitude',
        'longitude',
        'location_accuracy',
        'scheduled_start_at',
        'scheduled_end_at',
        'actual_start_at',
        'actual_end_at',
        'arrival_at',
        'completion_at',
        'assigned_technician_id',
        'supervisor_id',
        'territory_code',
        'is_billable',
        'estimated_cost',
        'actual_cost',
        'response_due_at',
        'restoration_due_at',
        'resolution_due_at',
        'is_sla_breached',
        'sla_breached_at',
        'created_by_user_id',
    ];

    protected $casts = [
        'scheduled_start_at' => 'datetime',
        'scheduled_end_at' => 'datetime',
        'actual_start_at' => 'datetime',
        'actual_end_at' => 'datetime',
        'arrival_at' => 'datetime',
        'completion_at' => 'datetime',
        'response_due_at' => 'datetime',
        'restoration_due_at' => 'datetime',
        'resolution_due_at' => 'datetime',
        'sla_breached_at' => 'datetime',
        'is_billable' => 'boolean',
        'is_sla_breached' => 'boolean',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function complaint()
    {
        return $this->belongsTo(CustomerComplaint::class);
    }

    public function incident()
    {
        return $this->belongsTo(SupportIncident::class);
    }

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function parentWorkOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'parent_work_order_id');
    }

    public function assignedTechnician()
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(WorkOrderStatusHistory::class)->latest();
    }

    public function assignments()
    {
        return $this->hasMany(WorkOrderAssignment::class);
    }

    public function gpsEvents()
    {
        return $this->hasMany(WorkOrderGpsEvent::class)->latest();
    }

    public function checklistResults()
    {
        return $this->hasMany(WorkOrderChecklistResult::class);
    }

    public function diagnostics()
    {
        return $this->hasMany(WorkOrderDiagnostic::class)->latest();
    }

    public function photos()
    {
        return $this->hasMany(WorkOrderPhoto::class)->latest();
    }

    public function materials()
    {
        return $this->hasMany(WorkOrderMaterial::class);
    }

    public function tools()
    {
        return $this->hasMany(WorkOrderTool::class);
    }

    public function equipmentReplacements()
    {
        return $this->hasMany(WorkOrderEquipmentReplacement::class)->latest();
    }

    public function timeEntries()
    {
        return $this->hasMany(WorkOrderTimeEntry::class);
    }

    public function customerConfirmation()
    {
        return $this->hasOne(WorkOrderCustomerConfirmation::class);
    }

    public function failures()
    {
        return $this->hasMany(WorkOrderFailure::class);
    }

    public function revisits()
    {
        return $this->hasMany(WorkOrderRevisit::class, 'original_work_order_id');
    }

    public function downtime()
    {
        return $this->hasOne(WorkOrderDowntime::class);
    }
}
