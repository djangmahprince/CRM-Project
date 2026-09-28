<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsCrmRecords;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Models\User;
use App\Notifications\RecordAssignedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    use ListsCrmRecords;

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Task::class);

        $user = $request->user();
        $sortable = ['subject' => 'subject', 'due_date' => 'due_date', 'status' => 'status', 'priority' => 'priority', 'created_at' => 'created_at'];
        $filters = $this->listFilters($request, $sortable);
        $query = Task::query()
            ->visibleTo($user)
            ->with(['owner:id,name', 'assignedTo:id,name', 'related'])
            ->when($request->filled('q'), function ($builder) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $builder->where(function ($inner) use ($term): void {
                    $inner->where('subject', 'like', $term)->orWhere('status', 'like', $term);
                });
            });

        if (! $this->applyRecentOrdering($query, $user, 'task', $request)) {
            $query->orderBy($filters['sort'], $filters['direction']);
        }

        return Inertia::render('Tasks/Index', [
            'tasks' => $query->paginate($filters['per_page'])->withQueryString(),
            'filters' => $filters,
            'can' => ['create' => $user->can('create', Task::class)],
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Task::class);

        return Inertia::render('Tasks/Create', [
            'picklists' => $this->picklists(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'prefill' => [
                'related_type' => $request->string('related_type')->toString() ?: null,
                'related_id' => $request->integer('related_id') ?: null,
                'status' => $request->string('status')->toString() ?: 'Not Started',
                'subject' => $request->string('subject')->toString() ?: null,
            ],
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $task = Task::query()->create($request->validated());
        $this->notifyAssignee($task);

        return redirect()->route('tasks.show', $task)->with('success', 'Task created.');
    }

    public function show(Request $request, Task $task): Response
    {
        Gate::authorize('view', $task);
        $task->load(['owner:id,name', 'assignedTo:id,name', 'related', 'contact:id,first_name,last_name']);
        $task->recordView($request->user());

        return Inertia::render('Tasks/Show', [
            'task' => $task,
            'can' => [
                'update' => $request->user()->can('update', $task),
                'delete' => $request->user()->can('delete', $task),
            ],
        ]);
    }

    public function edit(Task $task): Response
    {
        Gate::authorize('update', $task);

        return Inertia::render('Tasks/Edit', [
            'task' => $task,
            'picklists' => $this->picklists(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $previousAssignee = $task->assigned_to_id;
        $task->update($request->validated());

        if ((int) $previousAssignee !== (int) $task->assigned_to_id) {
            $this->notifyAssignee($task);
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Task updated.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        Gate::authorize('delete', $task);
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function picklists(): array
    {
        return [
            'task_statuses' => config('crm.task_statuses'),
            'task_priorities' => config('crm.task_priorities'),
            'related_types' => ['lead', 'account', 'contact', 'opportunity', 'case'],
        ];
    }

    private function notifyAssignee(Task $task): void
    {
        $assignee = $task->assignedTo;
        if ($assignee && (int) $assignee->id !== (int) auth()->id()) {
            $assignee->notify(new RecordAssignedNotification('task', $task->subject, route('tasks.show', $task)));
        }
    }
}
