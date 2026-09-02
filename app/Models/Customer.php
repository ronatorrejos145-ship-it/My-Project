<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_number',
        'account_number',
        'user_id',
        'customer_type',
        'status',
        'contact_person',
        'primary_phone',
        'secondary_phone',
        'email',
        'installation_address',
        'billing_address',
        'current_balance',
    ];

    protected function casts(): array
    {
        return [
            'status' => CustomerStatus::class,
            'current_balance' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
