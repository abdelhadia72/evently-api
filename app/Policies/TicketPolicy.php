<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, Event $event)
    {
        return $user->id === $event->organizer_id ||
               $user->hasPermission('tickets', 'read') ||
               $event->tickets()->where('user_id', $user->id)->exists();
    }

    public function view(User $user, Ticket $ticket)
    {
        return $user->id === $ticket->user_id ||
               $user->id === $ticket->event->organizer_id ||
               $user->hasPermission('tickets', 'read');
    }

    public function create(User $user, Event $event)
    {
        return true; // Allow all authenticated users to book tickets
    }

    public function checkIn(User $user, Ticket $ticket)
    {
        return $user->is_admin || $ticket->event->user_id === $user->id;
    }

    public function update(User $user, Ticket $ticket)
    {
        return $user->is_admin || $ticket->event->user_id === $user->id;
    }

    public function delete(User $user, ?Ticket $ticket = null, ?Event $event = null)
    {
        // User can cancel their own tickets or if they're admin
        return $user->id === $ticket->user_id || $user->is_admin;
    }
}
