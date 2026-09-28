<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsCrmRecords;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\User;
use App\Notifications\RecordAssignedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    use ListsCrmRecords;

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Event::class);

        $user = $request->user();
        $sortable = ['subject' => 'subject', 'starts_at' => 'starts_at', 'ends_at' => 'ends_at', 'created_at' => 'created_at'];
        $filters = $this->listFilters($request, $sortable, 'starts_at');
        $query = Event::query()
            ->visibleTo($user)
            ->with(['owner:id,name', 'assignedTo:id,name'])
            ->when($request->filled('q'), function ($builder) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $builder->where(function ($inner) use ($term): void {
                    $inner->where('subject', 'like', $term)->orWhere('location', 'like', $term);
                });
            });

        if (! $this->applyRecentOrdering($query, $user, 'event', $request)) {
            $query->orderBy($filters['sort'], $filters['direction']);
        }

        return Inertia::render('Events/Index', [
            'events' => $query->paginate($filters['per_page'])->withQueryString(),
            'filters' => $filters,
            'can' => ['create' => $user->can('create', Event::class)],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Event::class);

        return Inertia::render('Events/Create', [
            'picklists' => $this->picklists(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $event = Event::query()->create($request->validated());
        $this->notifyAssignee($event);

        return redirect()->route('events.show', $event)->with('success', 'Event created.');
    }

    public function show(Request $request, Event $event): Response
    {
        Gate::authorize('view', $event);
        $event->load(['owner:id,name', 'assignedTo:id,name', 'related', 'contact:id,first_name,last_name']);
        $event->recordView($request->user());

        return Inertia::render('Events/Show', [
            'event' => $event,
            'can' => [
                'update' => $request->user()->can('update', $event),
                'delete' => $request->user()->can('delete', $event),
            ],
        ]);
    }

    public function edit(Event $event): Response
    {
        Gate::authorize('update', $event);

        return Inertia::render('Events/Edit', [
            'event' => $event,
            'picklists' => $this->picklists(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $previousAssignee = $event->assigned_to_id;
        $event->update($request->validated());

        if ((int) $previousAssignee !== (int) $event->assigned_to_id) {
            $this->notifyAssignee($event);
        }

        return redirect()->route('events.show', $event)->with('success', 'Event updated.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        Gate::authorize('delete', $event);
        $event->delete();

        return redirect()->route('events.index')->with('success', 'Event deleted.');
    }

    public function reschedule(Request $request, Event $event): RedirectResponse
    {
        Gate::authorize('update', $event);

        $data = $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $event->update($data);

        return back()->with('success', 'Event rescheduled.');
    }

    /**
     * @return array<string, mixed>
     */
    private function picklists(): array
    {
        return [
            'show_as' => config('crm.show_as'),
            'related_types' => ['lead', 'account', 'contact', 'opportunity', 'case'],
        ];
    }

    private function notifyAssignee(Event $event): void
    {
        $assignee = $event->assignedTo;
        if ($assignee && (int) $assignee->id !== (int) auth()->id()) {
            $assignee->notify(new RecordAssignedNotification('event', $event->subject, route('events.show', $event)));
        }
    }
}
