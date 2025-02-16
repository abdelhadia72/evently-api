<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventAdditionNotification extends Notification
{
    use Queueable;

    protected $eventName;

    protected $eventId;

    protected $userId;

    public function __construct($eventName, $eventId, $userId)
    {
        $this->eventName = $eventName;
        $this->eventId = $eventId;
        $this->userId = $userId;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = 'Event Joined';
        $firstLine = 'You have successfully joined the event:';

        $hashCode = hash('sha256', $this->eventId.'|'.$this->userId);

        return (new MailMessage)
            ->subject($subject)
            ->line($firstLine)
            ->line($this->eventName)
            ->line('Your attendance verification code:')
            ->line($hashCode)
            ->line('Please keep this code safe - you\'ll need it to verify your attendance at the event.')
            ->line('You are receiving this notification because you joined this event.')
            ->line('Thank you for using our application!');
    }
}
