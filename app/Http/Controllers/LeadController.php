<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\RecentlyViewed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Lead::class);

        $user = $request->user();
        $perPage = min(
            max((int) $request->integer('per_page', config('crm.list_page_size')), 1),
            (int) config('crm.list_max_page_size'),
        );

        $sortable = [
            'last_name' => 'last_name',
            'company' => 'company',
            'lead_status' => 'lead_status',
            'created_at' => 'created_at',
            'email' => 'email',
        ];
        $sort = $sortable[$request->string('sort')->toString()] ?? 'created_at';
        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';

        $query = Lead::query()
            ->visibleTo($user)
            ->with(['owner:id,name,email'])
            ->when($request->filled('q'), function ($builder) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $builder->where(function ($inner) use ($term): void {
                    $inner->where('last_name', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('company', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            });

        $recentIds = RecentlyViewed::query()
            ->where('user_id', $user->id)
            ->where('viewable_type', 'lead')
            ->latest('viewed_at')
            ->limit(5)
            ->pluck('viewable_id');

        if ($request->boolean('recent') && $recentIds->isNotEmpty() && ! $request->filled('q')) {
            $query->whereIn('id', $recentIds);

            // Portable ordering for MySQL and SQLite (avoids FIELD()).
            $cases = $recentIds->values()->map(
                fn ($id, $index) => 'WHEN '.(int) $id.' THEN '.$index
            )->implode(' ');
            $query->orderByRaw("CASE id {$cases} END");
        } else {
            $query->orderBy($sort, $direction);
        }

        $leads = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => $perPage,
                'recent' => $request->boolean('recent'),
            ],
            'picklists' => $this->picklists(),
            'can' => [
                'create' => $user->can('create', Lead::class),
            ],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Lead::class);

        return Inertia::render('Leads/Create', [
            'picklists' => $this->picklists(),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $lead = Lead::query()->create($request->validated());

        return redirect()
            ->route('leads.show', $lead)
            ->with('success', 'Lead created.');
    }

    public function show(Request $request, Lead $lead): Response
    {
        Gate::authorize('view', $lead);

        $lead->load([
            'owner:id,name,email',
            'createdBy:id,name',
            'updatedBy:id,name',
            'convertedAccount:id,name',
            'convertedContact:id,first_name,last_name',
            'convertedOpportunity:id,name',
            'notes' => fn ($q) => $q->latest()->limit(20),
            'attachments' => fn ($q) => $q->latest()->limit(20),
            'tasks' => fn ($q) => $q->latest()->limit(10)->with('assignedTo:id,name'),
        ]);
        $lead->recordView($request->user());

        return Inertia::render('Leads/Show', [
            'lead' => $lead,
            'can' => [
                'update' => $request->user()->can('update', $lead),
                'delete' => $request->user()->can('delete', $lead),
                'convert' => ! $lead->converted && $request->user()->can('update', $lead),
            ],
        ]);
    }

    public function edit(Lead $lead): Response
    {
        Gate::authorize('update', $lead);

        return Inertia::render('Leads/Edit', [
            'lead' => $lead,
            'picklists' => $this->picklists(),
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $lead->update($request->validated());

        return redirect()
            ->route('leads.show', $lead)
            ->with('success', 'Lead updated.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        Gate::authorize('delete', $lead);

        $lead->delete();

        return redirect()
            ->route('leads.index')
            ->with('success', 'Lead deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function picklists(): array
    {
        return [
            'salutations' => config('crm.salutations'),
            'lead_statuses' => array_values(array_diff(config('crm.lead_statuses'), ['Converted'])),
            'lead_sources' => config('crm.lead_sources'),
            'ratings' => config('crm.ratings'),
            'industries' => config('crm.industries'),
        ];
    }
}
