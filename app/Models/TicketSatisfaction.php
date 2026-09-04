<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketSatisfaction extends Model
{
    use HasFactory;

    protected $table = 'ticket_satisfaction_reviews';

    protected $fillable = [
        'ticket_id',
        'customer_id',
        'rating_score',
        'comments',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
