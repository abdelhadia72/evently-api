<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Event extends Model
{
    use HasFactory, Notifiable;

    protected $with = ['organizer'];

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
        'category',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'max_attendees' => 'integer',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
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
            'event_id', // Foreign key on tickets table
            'id', // Foreign key on users table
            'id', // Local key on events table
            'user_id' // Local key on tickets table
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
            'status' => 'required|in:active,inactive,published,cancelled',
            'image_url' => 'nullable|string|url',
            'category' => 'required|in:music,art,food,social,sports,games,other',
        ];
    }
}
