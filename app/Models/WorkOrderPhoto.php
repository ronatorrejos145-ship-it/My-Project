<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'uploaded_by_user_id',
        'photo_category',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'latitude',
        'longitude',
        'caption',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
