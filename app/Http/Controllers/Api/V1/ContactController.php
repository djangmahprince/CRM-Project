<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('viewAny', Contact::class), 403);

        $contacts = Contact::query()
            ->visibleTo($request->user())
            ->with(['owner:id,name,email', 'account:id,name'])
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 25), 100));

        return ContactResource::collection($contacts);
    }

    public function store(Request $request): ContactResource
    {
        abort_unless($request->user()->can('create', Contact::class), 403);

        $data = $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'salutation' => ['nullable', 'string', 'max:20', Rule::in(config('crm.salutations'))],
            'first_name' => ['nullable', 'string', 'max:40'],
            'middle_name' => ['nullable', 'string', 'max:40'],
            'last_name' => ['required', 'string', 'max:80'],
            'title' => ['nullable', 'string', 'max:128'],
            'department' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:40'],
            'mobile' => ['nullable', 'string', 'max:40'],
            'home_phone' => ['nullable', 'string', 'max:40'],
            'other_phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:80'],
            'fax' => ['nullable', 'string', 'max:40'],
            'reports_to_id' => ['nullable', 'integer', 'exists:contacts,id'],
            'assistant' => ['nullable', 'string', 'max:80'],
            'asst_phone' => ['nullable', 'string', 'max:40'],
            'mailing_street' => ['nullable', 'string', 'max:255'],
            'mailing_city' => ['nullable', 'string', 'max:80'],
            'mailing_state' => ['nullable', 'string', 'max:80'],
            'mailing_postal_code' => ['nullable', 'string', 'max:20'],
            'mailing_country' => ['nullable', 'string', 'max:80'],
            'other_street' => ['nullable', 'string', 'max:255'],
            'other_city' => ['nullable', 'string', 'max:80'],
            'other_state' => ['nullable', 'string', 'max:80'],
            'other_postal_code' => ['nullable', 'string', 'max:20'],
            'other_country' => ['nullable', 'string', 'max:80'],
            'lead_source' => ['nullable', 'string', Rule::in(config('crm.lead_sources'))],
            'birthdate' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $contact = Contact::query()->create($data);

        return new ContactResource($contact->load(['owner:id,name,email', 'account:id,name']));
    }

    public function show(Request $request, Contact $contact): ContactResource
    {
        abort_unless($request->user()->can('view', $contact), 403);

        return new ContactResource($contact->load(['owner:id,name,email', 'account:id,name']));
    }

    public function update(Request $request, Contact $contact): ContactResource
    {
        abort_unless($request->user()->can('update', $contact), 403);

        $data = $request->validate([
            'account_id' => ['sometimes', 'integer', 'exists:accounts,id'],
            'salutation' => ['nullable', 'string', 'max:20', Rule::in(config('crm.salutations'))],
            'first_name' => ['nullable', 'string', 'max:40'],
            'middle_name' => ['nullable', 'string', 'max:40'],
            'last_name' => ['sometimes', 'string', 'max:80'],
            'title' => ['nullable', 'string', 'max:128'],
            'department' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:40'],
            'mobile' => ['nullable', 'string', 'max:40'],
            'home_phone' => ['nullable', 'string', 'max:40'],
            'other_phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:80'],
            'fax' => ['nullable', 'string', 'max:40'],
            'reports_to_id' => ['nullable', 'integer', 'exists:contacts,id', Rule::notIn([(int) $contact->id])],
            'assistant' => ['nullable', 'string', 'max:80'],
            'asst_phone' => ['nullable', 'string', 'max:40'],
            'mailing_street' => ['nullable', 'string', 'max:255'],
            'mailing_city' => ['nullable', 'string', 'max:80'],
            'mailing_state' => ['nullable', 'string', 'max:80'],
            'mailing_postal_code' => ['nullable', 'string', 'max:20'],
            'mailing_country' => ['nullable', 'string', 'max:80'],
            'other_street' => ['nullable', 'string', 'max:255'],
            'other_city' => ['nullable', 'string', 'max:80'],
            'other_state' => ['nullable', 'string', 'max:80'],
            'other_postal_code' => ['nullable', 'string', 'max:20'],
            'other_country' => ['nullable', 'string', 'max:80'],
            'lead_source' => ['nullable', 'string', Rule::in(config('crm.lead_sources'))],
            'birthdate' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $contact->update($data);

        return new ContactResource($contact->fresh()->load(['owner:id,name,email', 'account:id,name']));
    }

    public function destroy(Request $request, Contact $contact): Response
    {
        abort_unless($request->user()->can('delete', $contact), 403);
        $contact->delete();

        return response()->noContent();
    }
}
