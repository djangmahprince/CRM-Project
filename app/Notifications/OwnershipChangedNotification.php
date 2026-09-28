<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OwnershipChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $objectType,
        public string $recordLabel,
        public string $url,
        public string $role,
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
        $line = $this->role === 'previous'
            ? 'Ownership of this '.$this->objectType.' was transferred away from you: '.$this->recordLabel
            : 'You are now the owner of this '.$this->objectType.': '.$this->recordLabel;

        return (new MailMessage)
            ->subject('Ownership changed: '.$this->recordLabel)
            ->line($line)
            ->action('Open record', $this->url);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'object_type' => $this->objectType,
            'record_label' => $this->recordLabel,
            'url' => $this->url,
            'role' => $this->role,
        ];
    }
}
