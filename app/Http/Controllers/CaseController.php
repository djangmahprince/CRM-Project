<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsCrmRecords;
use App\Http\Requests\StoreCaseRequest;
use App\Http\Requests\UpdateCaseRequest;
use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CaseController extends Controller
{
    use ListsCrmRecords;

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', CrmCase::class);

        $user = $request->user();
        $sortable = [
            'case_number' => 'case_number',
            'subject' => 'subject',
            'status' => 'status',
            'priority' => 'priority',
            'created_at' => 'created_at',
        ];
        $filters = $this->listFilters($request, $sortable);
        $query = CrmCase::query()
            ->visibleTo($user)
            ->with(['owner:id,name,email', 'account:id,name', 'contact:id,first_name,last_name'])
            ->when($request->filled('q'), function ($builder) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $builder->where(function ($inner) use ($term): void {
                    $inner->where('case_number', 'like', $term)
                        ->orWhere('subject', 'like', $term)
                        ->orWhere('status', 'like', $term);
                });
            });

        if (! $this->applyRecentOrdering($query, $user, 'case', $request)) {
            $query->orderBy($filters['sort'], $filters['direction']);
        }

        return Inertia::render('Cases/Index', [
            'cases' => $query->paginate($filters['per_page'])->withQueryString(),
            'filters' => $filters,
            'can' => ['create' => $user->can('create', CrmCase::class)],
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', CrmCase::class);

        return Inertia::render('Cases/Create', [
            'picklists' => $this->picklists(),
            'accounts' => Account::query()->visibleTo($request->user())->orderBy('name')->limit(200)->get(['id', 'name']),
            'contacts' => Contact::query()->visibleTo($request->user())->orderBy('last_name')->limit(200)->get(['id', 'first_name', 'last_name', 'account_id']),
            'prefill_account_id' => $request->integer('account_id') ?: null,
            'prefill_contact_id' => $request->integer('contact_id') ?: null,
        ]);
    }

    public function store(StoreCaseRequest $request): RedirectResponse
    {
        $case = CrmCase::query()->create($request->validated());

        return redirect()->route('cases.show', $case)->with('success', 'Case created.');
    }

    public function show(Request $request, CrmCase $case): Response
    {
        Gate::authorize('view', $case);

        $case->load([
            'owner:id,name,email',
            'createdBy:id,name',
            'updatedBy:id,name',
            'account:id,name',
            'contact:id,first_name,last_name',
            'notes' => fn ($q) => $q->latest()->limit(20),
            'attachments' => fn ($q) => $q->latest()->limit(20),
        ]);
        $case->recordView($request->user());

        return Inertia::render('Cases/Show', [
            'caseRecord' => $case,
            'can' => [
                'update' => $request->user()->can('update', $case),
                'delete' => $request->user()->can('delete', $case),
                'close' => $request->user()->can('close', $case),
                'reopen' => $request->user()->can('reopen', $case),
            ],
        ]);
    }

    public function edit(Request $request, CrmCase $case): Response
    {
        Gate::authorize('update', $case);

        return Inertia::render('Cases/Edit', [
            'caseRecord' => $case,
            'picklists' => $this->picklists(),
            'accounts' => Account::query()->visibleTo($request->user())->orderBy('name')->limit(200)->get(['id', 'name']),
            'contacts' => Contact::query()->visibleTo($request->user())->orderBy('last_name')->limit(200)->get(['id', 'first_name', 'last_name', 'account_id']),
        ]);
    }

    public function update(UpdateCaseRequest $request, CrmCase $case): RedirectResponse
    {
        $case->update($request->validated());

        return redirect()->route('cases.show', $case)->with('success', 'Case updated.');
    }

    public function destroy(CrmCase $case): RedirectResponse
    {
        Gate::authorize('delete', $case);
        $case->delete();

        return redirect()->route('cases.index')->with('success', 'Case deleted.');
    }

    public function close(CrmCase $case): RedirectResponse
    {
        Gate::authorize('close', $case);
        $case->update(['status' => 'Closed']);

        return redirect()->route('cases.show', $case)->with('success', 'Case closed.');
    }

    public function reopen(CrmCase $case): RedirectResponse
    {
        Gate::authorize('reopen', $case);
        $case->update(['status' => 'Working']);

        return redirect()->route('cases.show', $case)->with('success', 'Case reopened.');
    }

    /**
     * @return array<string, mixed>
     */
    private function picklists(): array
    {
        return [
            'case_statuses' => array_values(array_diff(config('crm.case_statuses'), ['Closed'])),
            'priorities' => config('crm.priorities'),
            'case_types' => config('crm.case_types'),
            'case_origins' => config('crm.case_origins'),
            'case_reasons' => config('crm.case_reasons'),
        ];
    }
}
