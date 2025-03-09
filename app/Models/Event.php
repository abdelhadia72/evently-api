<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Event extends Model
{
    use HasFactory, Notifiable;

    protected $with = ['organizer', 'category'];

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'location',
        'organizer_id',
        'status',
        'max_attendees',
        'image_url',
        'category_id',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'max_attendees' => 'integer',
        'status' => EventStatus::class,
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function attendees()
    {
        return $this->hasManyThrough(
            User::class,
            Ticket::class,
            'event_id',
            'id',
            'id',
            'user_id'
        )->where('tickets.status', 'active');
    }

    public function rules($id = null)
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'location' => 'required|string',
            'max_attendees' => 'nullable|integer|min:1',
            'status' => 'required|string|in:'.implode(',', EventStatus::values()),
            'image_url' => 'nullable|string|url',
            'category_id' => 'required|exists:categories,id',
        ];
    }

    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function totalAvailableTickets()
    {
        $availableTickets = 0;

        foreach ($this->ticketTypes as $ticketType) {
            if ($ticketType->is_active) {
                $availableTickets += $ticketType->availableQuantity();
            }
        }

        return $availableTickets;
    }
}
