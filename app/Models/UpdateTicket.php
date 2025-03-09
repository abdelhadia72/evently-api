<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateTicket extends Model
{
    protected $fillable = [
        'ticket_number',
        'event_id',
        'user_id',
        'order_id',
        'ticket_type_id',
        'price_paid',
        'status',
        'check_in_time',
        'qr_code',
    ];

    protected $casts = [
        'price_paid' => 'decimal:2',
        'check_in_time' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }
}
