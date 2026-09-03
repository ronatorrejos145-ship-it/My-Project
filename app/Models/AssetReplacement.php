<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetReplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'old_asset_id',
        'new_asset_id',
        'customer_id',
        'installation_id',
        'replaced_by',
        'replaced_at',
        'reason',
        'old_asset_condition',
        'notes',
    ];

    protected $casts = [
        'replaced_at' => 'datetime',
    ];

    public function oldAsset()
    {
        return $this->belongsTo(Asset::class, 'old_asset_id');
    }

    public function newAsset()
    {
        return $this->belongsTo(Asset::class, 'new_asset_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function installation()
    {
        return $this->belongsTo(InstallationWorkOrder::class, 'installation_id');
    }
}
