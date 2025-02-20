<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketBookedNotification extends Notification
{
    use Queueable;

    protected $ticket;

    protected $event;

    public function __construct(Ticket $ticket, Event $event)
    {
        $this->ticket = $ticket;
        $this->event = $event;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Ticket for '.$this->event->title)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your ticket has been booked successfully.')
            ->line('Event Details:')
            ->line('🎫 '.$this->event->title)
            ->line('📅 '.$this->event->start_date->format('F j, Y, g:i a'))
            ->line('📍 '.$this->event->location)
            ->line('Ticket Information:')
            ->line('🎟️ Ticket #: '.$this->ticket->ticket_number)
            ->line('If you did not book this ticket, please contact support.');
    }
}
