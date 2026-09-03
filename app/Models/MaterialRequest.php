<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'requester_id',
        'warehouse_id',
        'priority',
        'status',
        'required_date',
        'purpose',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'required_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(MaterialRequestItem::class, 'material_request_id');
    }
}
