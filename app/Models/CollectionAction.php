<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'action_number',
        'collection_account_id',
        'customer_id',
        'service_account_id',
        'invoice_id',
        'action_type',
        'collector_user_id',
        'action_at',
        'result_status',
        'notes',
        'next_action_date',
    ];

    protected $casts = [
        'action_at' => 'datetime',
        'next_action_date' => 'date',
    ];

    public function collectionAccount()
    {
        return $this->belongsTo(CollectionAccount::class, 'collection_account_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_user_id');
    }
}
