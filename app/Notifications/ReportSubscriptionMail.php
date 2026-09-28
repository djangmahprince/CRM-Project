<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportSubscriptionMail extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Report $report) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Scheduled report: '.$this->report->name)
            ->line('Your subscribed report "'.$this->report->name.'" is ready.')
            ->action('Open report', route('report-builder.show', $this->report));
    }
}
