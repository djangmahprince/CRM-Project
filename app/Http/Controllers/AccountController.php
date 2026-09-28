<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsCrmRecords;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    use ListsCrmRecords;

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Account::class);

        $user = $request->user();
        $sortable = ['name' => 'name', 'type' => 'type', 'industry' => 'industry', 'created_at' => 'created_at'];
        $filters = $this->listFilters($request, $sortable);
        $query = Account::query()
            ->visibleTo($user)
            ->with(['owner:id,name,email'])
            ->when($request->filled('q'), function ($builder) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $builder->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhere('website', 'like', $term);
                });
            });

        if (! $this->applyRecentOrdering($query, $user, 'account', $request)) {
            $query->orderBy($filters['sort'], $filters['direction']);
        }

        return Inertia::render('Accounts/Index', [
            'accounts' => $query->paginate($filters['per_page'])->withQueryString(),
            'filters' => $filters,
            'can' => ['create' => $user->can('create', Account::class)],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Account::class);

        return Inertia::render('Accounts/Create', [
            'picklists' => $this->picklists(),
            'accounts' => Account::query()->orderBy('name')->limit(200)->get(['id', 'name']),
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $account = Account::query()->create($request->validated());

        return redirect()->route('accounts.show', $account)->with('success', 'Account created.');
    }

    public function show(Request $request, Account $account): Response
    {
        Gate::authorize('view', $account);

        $account->load([
            'owner:id,name,email',
            'createdBy:id,name',
            'updatedBy:id,name',
            'parentAccount:id,name',
            'contacts:id,account_id,first_name,last_name,email,title',
            'opportunities:id,account_id,name,stage,amount,close_date',
            'cases:id,account_id,case_number,subject,status,priority',
            'notes' => fn ($q) => $q->latest()->limit(20),
            'attachments' => fn ($q) => $q->latest()->limit(20),
        ]);
        $account->recordView($request->user());

        return Inertia::render('Accounts/Show', [
            'account' => $account,
            'can' => [
                'update' => $request->user()->can('update', $account),
                'delete' => $request->user()->can('delete', $account),
            ],
        ]);
    }

    public function edit(Account $account): Response
    {
        Gate::authorize('update', $account);

        return Inertia::render('Accounts/Edit', [
            'account' => $account,
            'picklists' => $this->picklists(),
            'accounts' => Account::query()
                ->whereKeyNot($account->id)
                ->orderBy('name')
                ->limit(200)
                ->get(['id', 'name']),
        ]);
    }

    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        $account->update($request->validated());

        return redirect()->route('accounts.show', $account)->with('success', 'Account updated.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        Gate::authorize('delete', $account);
        $account->delete();

        return redirect()->route('accounts.index')->with('success', 'Account deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function picklists(): array
    {
        return [
            'account_types' => config('crm.account_types'),
            'industries' => config('crm.industries'),
        ];
    }
}
