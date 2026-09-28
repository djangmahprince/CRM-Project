<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListsCrmRecords;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Account;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    use ListsCrmRecords;

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Contact::class);

        $user = $request->user();
        $sortable = ['last_name' => 'last_name', 'email' => 'email', 'title' => 'title', 'created_at' => 'created_at'];
        $filters = $this->listFilters($request, $sortable);
        $query = Contact::query()
            ->visibleTo($user)
            ->with(['owner:id,name,email', 'account:id,name'])
            ->when($request->filled('q'), function ($builder) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $builder->where(function ($inner) use ($term): void {
                    $inner->where('last_name', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            });

        if (! $this->applyRecentOrdering($query, $user, 'contact', $request)) {
            $query->orderBy($filters['sort'], $filters['direction']);
        }

        return Inertia::render('Contacts/Index', [
            'contacts' => $query->paginate($filters['per_page'])->withQueryString(),
            'filters' => $filters,
            'can' => ['create' => $user->can('create', Contact::class)],
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Contact::class);

        return Inertia::render('Contacts/Create', [
            'picklists' => $this->picklists(),
            'accounts' => Account::query()->visibleTo($request->user())->orderBy('name')->limit(200)->get(['id', 'name']),
            'prefill_account_id' => $request->integer('account_id') ?: null,
        ]);
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $contact = Contact::query()->create($request->validated());

        return redirect()->route('contacts.show', $contact)->with('success', 'Contact created.');
    }

    public function show(Request $request, Contact $contact): Response
    {
        Gate::authorize('view', $contact);

        $contact->load([
            'owner:id,name,email',
            'createdBy:id,name',
            'updatedBy:id,name',
            'account:id,name',
            'reportsTo:id,first_name,last_name',
            'cases:id,contact_id,case_number,subject,status',
            'notes' => fn ($q) => $q->latest()->limit(20),
            'attachments' => fn ($q) => $q->latest()->limit(20),
        ]);
        $contact->recordView($request->user());

        return Inertia::render('Contacts/Show', [
            'contact' => $contact,
            'can' => [
                'update' => $request->user()->can('update', $contact),
                'delete' => $request->user()->can('delete', $contact),
            ],
        ]);
    }

    public function edit(Request $request, Contact $contact): Response
    {
        Gate::authorize('update', $contact);

        return Inertia::render('Contacts/Edit', [
            'contact' => $contact,
            'picklists' => $this->picklists(),
            'accounts' => Account::query()->visibleTo($request->user())->orderBy('name')->limit(200)->get(['id', 'name']),
            'contacts' => Contact::query()
                ->visibleTo($request->user())
                ->whereKeyNot($contact->id)
                ->orderBy('last_name')
                ->limit(200)
                ->get(['id', 'first_name', 'last_name']),
        ]);
    }

    public function update(UpdateContactRequest $request, Contact $contact): RedirectResponse
    {
        $contact->update($request->validated());

        return redirect()->route('contacts.show', $contact)->with('success', 'Contact updated.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        Gate::authorize('delete', $contact);
        $contact->delete();

        return redirect()->route('contacts.index')->with('success', 'Contact deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function picklists(): array
    {
        return [
            'salutations' => config('crm.salutations'),
            'lead_sources' => config('crm.lead_sources'),
        ];
    }
}
