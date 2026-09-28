<?php

namespace App\Jobs;

use App\Models\Task;
use App\Notifications\TaskDueNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTaskReminders implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Task::query()
            ->where('reminder_set', true)
            ->whereNull('reminder_sent_at')
            ->whereNotNull('reminder_at')
            ->where('reminder_at', '<=', now())
            ->where('status', '!=', 'Completed')
            ->with('assignedTo')
            ->each(function (Task $task): void {
                $task->assignedTo?->notify(new TaskDueNotification($task));
                $task->forceFill(['reminder_sent_at' => now()])->save();
            });
    }
}
