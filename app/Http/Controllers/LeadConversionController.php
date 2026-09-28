<?php

namespace App\Http\Controllers;

use App\Actions\ConvertLeadAction;
use App\Http\Requests\ConvertLeadRequest;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LeadConversionController extends Controller
{
    public function create(Request $request, Lead $lead): Response
    {
        Gate::authorize('update', $lead);

        abort_if($lead->converted, 403, 'Lead is already converted.');

        $matchedAccounts = Account::query()
            ->visibleTo($request->user())
            ->where('name', 'like', '%'.$lead->company.'%')
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'website']);

        $matchedContacts = Contact::query()
            ->visibleTo($request->user())
            ->when($lead->email, fn ($q) => $q->where('email', $lead->email))
            ->when(! $lead->email, fn ($q) => $q->where('last_name', $lead->last_name))
            ->with('account:id,name')
            ->limit(10)
            ->get(['id', 'account_id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Leads/Convert', [
            'lead' => $lead,
            'matchedAccounts' => $matchedAccounts,
            'matchedContacts' => $matchedContacts,
            'accounts' => Account::query()->visibleTo($request->user())->orderBy('name')->limit(200)->get(['id', 'name']),
            'contacts' => Contact::query()->visibleTo($request->user())->orderBy('last_name')->limit(200)->get(['id', 'account_id', 'first_name', 'last_name', 'email']),
            'stages' => config('crm.opportunity_stages'),
        ]);
    }

    public function store(ConvertLeadRequest $request, Lead $lead, ConvertLeadAction $action): RedirectResponse
    {
        abort_if($lead->converted, 403, 'Lead is already converted.');

        $converted = $action->handle($lead, $request->user(), $request->validated());

        return redirect()
            ->route('leads.show', $converted)
            ->with('success', 'Lead converted successfully.');
    }
}
