<?php

namespace App\Jobs;

use App\Models\ReportSubscription;
use App\Notifications\ReportSubscriptionMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendReportSubscriptions implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $now = now();

        ReportSubscription::query()
            ->with(['report', 'user'])
            ->get()
            ->each(function (ReportSubscription $subscription) use ($now): void {
                if (! $subscription->user || ! $subscription->report) {
                    return;
                }

                $due = match ($subscription->frequency) {
                    'weekly' => (int) $now->dayOfWeek === (int) ($subscription->send_day ?? 1)
                        && $now->format('H:i') >= substr((string) $subscription->send_time, 0, 5)
                        && ($subscription->last_sent_at === null || $subscription->last_sent_at->lt($now->copy()->startOfDay())),
                    default => $now->format('H:i') >= substr((string) $subscription->send_time, 0, 5)
                        && ($subscription->last_sent_at === null || $subscription->last_sent_at->lt($now->copy()->startOfDay())),
                };

                if (! $due) {
                    return;
                }

                $subscription->user->notify(new ReportSubscriptionMail($subscription->report));
                $subscription->forceFill(['last_sent_at' => $now])->save();
            });
    }
}
