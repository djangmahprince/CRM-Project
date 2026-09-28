<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecordAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $objectType,
        public string $subject,
        public string $url,
    ) {
        $this->afterCommit();
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Assigned: '.$this->subject)
            ->line('You were assigned a '.$this->objectType.': '.$this->subject)
            ->action('Open record', $this->url);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'object_type' => $this->objectType,
            'subject' => $this->subject,
            'url' => $this->url,
        ];
    }
}
