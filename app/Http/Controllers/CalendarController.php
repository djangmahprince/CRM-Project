<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function __invoke(Request $request): Response
    {
        Gate::authorize('viewAny', Event::class);

        $view = $request->string('view')->toString() ?: 'month';
        if (! in_array($view, ['day', 'week', 'month', 'table'], true)) {
            $view = 'month';
        }

        $anchor = $request->filled('date')
            ? Carbon::parse($request->string('date')->toString())
            : now();

        [$start, $end] = match ($view) {
            'day' => [$anchor->copy()->startOfDay(), $anchor->copy()->endOfDay()],
            'month' => [$anchor->copy()->startOfMonth()->startOfWeek(), $anchor->copy()->endOfMonth()->endOfWeek()],
            'table' => [$anchor->copy()->startOfMonth(), $anchor->copy()->endOfMonth()],
            default => [$anchor->copy()->startOfWeek(), $anchor->copy()->endOfWeek()],
        };

        $user = $request->user();

        $events = Event::query()
            ->visibleTo($user)
            ->with(['assignedTo:id,name'])
            ->where('starts_at', '<=', $end)
            ->where('ends_at', '>=', $start)
            ->orderBy('starts_at')
            ->get();

        $tasks = Task::query()
            ->visibleTo($user)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('due_date')
            ->get(['id', 'subject', 'due_date', 'status', 'priority']);

        $days = [];
        $cursor = $start->copy()->startOfDay();
        $last = $end->copy()->startOfDay();
        while ($cursor->lte($last)) {
            $dateKey = $cursor->toDateString();
            $days[] = [
                'date' => $dateKey,
                'label' => $cursor->format('D j'),
                'is_today' => $dateKey === now()->toDateString(),
                'is_current_month' => $cursor->month === $anchor->month,
                'events' => $events
                    ->filter(fn (Event $event) => $event->starts_at->toDateString() <= $dateKey
                        && $event->ends_at->toDateString() >= $dateKey)
                    ->values()
                    ->map(fn (Event $event) => [
                        'id' => $event->id,
                        'subject' => $event->subject,
                        'starts_at' => $event->starts_at?->toIso8601String(),
                        'ends_at' => $event->ends_at?->toIso8601String(),
                        'all_day' => (bool) $event->all_day,
                        'color' => ['#0f766e', '#0369a1', '#b45309', '#be123c', '#7c3aed'][$event->id % 5],
                        'is_private' => (bool) $event->is_private,
                        'location' => $event->location,
                    ])
                    ->all(),
                'tasks' => $tasks
                    ->filter(fn (Task $task) => optional($task->due_date)?->toDateString() === $dateKey)
                    ->values()
                    ->map(fn (Task $task) => [
                        'id' => $task->id,
                        'subject' => $task->subject,
                        'status' => $task->status,
                        'priority' => $task->priority,
                    ])
                    ->all(),
            ];
            $cursor->addDay();
        }

        return Inertia::render('Calendar/Index', [
            'view' => $view,
            'date' => $anchor->toDateString(),
            'range' => [
                'start' => $start->toIso8601String(),
                'end' => $end->toIso8601String(),
            ],
            'navigation' => [
                'prev' => $this->shiftDate($anchor, $view, -1),
                'next' => $this->shiftDate($anchor, $view, 1),
                'today' => now()->toDateString(),
            ],
            'days' => $days,
            'events' => $events,
            'tasks' => $tasks,
            'can' => [
                'create_event' => $user->can('create', Event::class),
                'create_task' => $user->can('create', Task::class),
            ],
        ]);
    }

    private function shiftDate(Carbon $anchor, string $view, int $direction): string
    {
        $copy = $anchor->copy();

        return match ($view) {
            'day' => $copy->addDays($direction)->toDateString(),
            'month', 'table' => $copy->addMonths($direction)->toDateString(),
            default => $copy->addWeeks($direction)->toDateString(),
        };
    }
}
