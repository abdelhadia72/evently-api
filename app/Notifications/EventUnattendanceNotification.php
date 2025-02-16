<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventUnattendanceNotification extends Notification
{
    use Queueable;

    protected $eventName;

    protected $isJoining;

    public function __construct($eventName, $isJoining = false)
    {
        $this->eventName = $eventName;
        $this->isJoining = $isJoining;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = $this->isJoining ? 'Event Joined' : 'New Event Added';
        $firstLine = $this->isJoining ?
            'You have successfully joined the event:' :
            'A new event has been added:';

        return (new MailMessage)
            ->subject($subject)
            ->line($firstLine)
            ->line($this->eventName)
            ->line('You are receiving this notification because '.
                ($this->isJoining ? 'you joined this event.' : 'you are subscribed to event updates.'))
            ->line('Thank you for using our application!');
    }
}
