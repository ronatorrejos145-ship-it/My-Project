<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'asset_id',
        'source_type',
        'source_id',
        'destination_type',
        'destination_id',
        'status',
        'authorized_by',
        'transferred_by',
        'received_by',
        'transferred_at',
        'received_at',
        'condition_on_transfer',
        'notes',
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function authorizer()
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function transferrer()
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
