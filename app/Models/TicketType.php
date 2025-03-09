<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    protected $table = 'ticket_types';

    protected $fillable = [
        'event_id',
        'name',
        'price',
        'quantity',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function availableQuantity()
    {
        $soldTickets = $this->hasMany(Ticket::class)
            ->whereHas('order', function ($query) {
                $query->whereNotIn('status', ['cancelled', 'refunded']);
            })
            ->count();

        return max(0, $this->quantity - $soldTickets);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
