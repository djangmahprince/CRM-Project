<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsCrmRecords;
use App\Http\Requests\StoreOpportunityRequest;
use App\Http\Requests\UpdateOpportunityRequest;
use App\Http\Requests\UpdateOpportunityStageRequest;
use App\Models\Account;
use App\Models\Opportunity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityController extends Controller
{
    use ListsCrmRecords;

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Opportunity::class);

        $user = $request->user();
        $sortable = [
            'name' => 'name',
            'stage' => 'stage',
            'amount' => 'amount',
            'close_date' => 'close_date',
            'created_at' => 'created_at',
        ];
        $filters = $this->listFilters($request, $sortable);
        $query = Opportunity::query()
            ->visibleTo($user)
            ->with(['owner:id,name,email', 'account:id,name'])
            ->when($request->filled('q'), function ($builder) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $builder->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('stage', 'like', $term)
                        ->orWhere('next_step', 'like', $term);
                });
            });

        if (! $this->applyRecentOrdering($query, $user, 'opportunity', $request)) {
            $query->orderBy($filters['sort'], $filters['direction']);
        }

        return Inertia::render('Opportunities/Index', [
            'opportunities' => $query->paginate($filters['per_page'])->withQueryString(),
            'filters' => $filters,
            'stages' => array_keys(config('crm.opportunity_stages')),
            'can' => ['create' => $user->can('create', Opportunity::class)],
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Opportunity::class);

        return Inertia::render('Opportunities/Create', [
            'picklists' => $this->picklists(),
            'accounts' => Account::query()->visibleTo($request->user())->orderBy('name')->limit(200)->get(['id', 'name']),
            'prefill_account_id' => $request->integer('account_id') ?: null,
        ]);
    }

    public function store(StoreOpportunityRequest $request): RedirectResponse
    {
        $opportunity = Opportunity::query()->create($request->validated());

        return redirect()->route('opportunities.show', $opportunity)->with('success', 'Opportunity created.');
    }

    public function show(Request $request, Opportunity $opportunity): Response
    {
        Gate::authorize('view', $opportunity);

        $opportunity->load([
            'owner:id,name,email',
            'createdBy:id,name',
            'updatedBy:id,name',
            'account:id,name',
            'stageHistory' => fn ($q) => $q->latest()->limit(20)->with('changedBy:id,name'),
            'notes' => fn ($q) => $q->latest()->limit(20),
            'attachments' => fn ($q) => $q->latest()->limit(20),
        ]);
        $opportunity->append('expected_revenue');
        $opportunity->recordView($request->user());

        return Inertia::render('Opportunities/Show', [
            'opportunity' => $opportunity,
            'stages' => config('crm.opportunity_stages'),
            'can' => [
                'update' => $request->user()->can('update', $opportunity),
                'delete' => $request->user()->can('delete', $opportunity),
                'clone' => $request->user()->can('create', Opportunity::class),
            ],
        ]);
    }

    public function edit(Request $request, Opportunity $opportunity): Response
    {
        Gate::authorize('update', $opportunity);

        return Inertia::render('Opportunities/Edit', [
            'opportunity' => $opportunity->append('expected_revenue'),
            'picklists' => $this->picklists(),
            'accounts' => Account::query()->visibleTo($request->user())->orderBy('name')->limit(200)->get(['id', 'name']),
        ]);
    }

    public function update(UpdateOpportunityRequest $request, Opportunity $opportunity): RedirectResponse
    {
        $opportunity->update($request->validated());

        return redirect()->route('opportunities.show', $opportunity)->with('success', 'Opportunity updated.');
    }

    public function destroy(Opportunity $opportunity): RedirectResponse
    {
        Gate::authorize('delete', $opportunity);
        $opportunity->delete();

        return redirect()->route('opportunities.index')->with('success', 'Opportunity deleted.');
    }

    public function updateStage(UpdateOpportunityStageRequest $request, Opportunity $opportunity): RedirectResponse
    {
        $opportunity->update(['stage' => $request->validated('stage')]);

        return back()->with('success', 'Opportunity stage updated.');
    }

    public function clone(Request $request, Opportunity $opportunity): RedirectResponse
    {
        Gate::authorize('create', Opportunity::class);
        Gate::authorize('view', $opportunity);

        $clone = DB::transaction(function () use ($request, $opportunity) {
            $copy = $opportunity->replicate([
                'is_closed',
                'is_won',
            ]);
            $copy->name = $opportunity->name.' (Copy)';
            $copy->stage = 'Qualification';
            $copy->close_date = now()->addMonth()->toDateString();
            $copy->owner_id = $request->user()->id;
            $copy->created_by = $request->user()->id;
            $copy->updated_by = $request->user()->id;
            $copy->save();

            return $copy;
        });

        return redirect()
            ->route('opportunities.edit', $clone)
            ->with('success', 'Opportunity cloned. Review and save.');
    }

    /**
     * @return array<string, mixed>
     */
    private function picklists(): array
    {
        return [
            'stages' => config('crm.opportunity_stages'),
            'opportunity_types' => config('crm.opportunity_types'),
            'lead_sources' => config('crm.lead_sources'),
        ];
    }
}
