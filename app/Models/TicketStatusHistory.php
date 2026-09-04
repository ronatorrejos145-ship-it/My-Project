<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'previous_status',
        'new_status',
        'reason',
        'changed_by',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }
}
