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

        $view = $request->string('view')->toString() ?: 'week';
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

        return Inertia::render('Calendar/Index', [
            'view' => $view,
            'date' => $anchor->toDateString(),
            'range' => [
                'start' => $start->toIso8601String(),
                'end' => $end->toIso8601String(),
            ],
            'events' => $events,
            'tasks' => $tasks,
            'can' => [
                'create_event' => $user->can('create', Event::class),
                'create_task' => $user->can('create', Task::class),
            ],
        ]);
    }
}
