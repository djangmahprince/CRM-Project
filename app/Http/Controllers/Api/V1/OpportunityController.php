<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OpportunityResource;
use App\Models\Opportunity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class OpportunityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('viewAny', Opportunity::class), 403);

        $opportunities = Opportunity::query()
            ->visibleTo($request->user())
            ->with(['owner:id,name,email', 'account:id,name'])
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 25), 100));

        return OpportunityResource::collection($opportunities);
    }

    public function store(Request $request): OpportunityResource
    {
        abort_unless($request->user()->can('create', Opportunity::class), 403);

        $closedStages = ['Closed Won', 'Closed Lost'];
        $stage = $request->string('stage')->toString();
        $isOpen = ! in_array($stage, $closedStages, true);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'close_date' => [
                'required',
                'date',
                Rule::when($isOpen, ['after_or_equal:today']),
            ],
            'stage' => ['required', 'string', Rule::in(array_keys(config('crm.opportunity_stages')))],
            'type' => ['nullable', 'string', Rule::in(config('crm.opportunity_types'))],
            'lead_source' => ['nullable', 'string', Rule::in(config('crm.lead_sources'))],
            'next_step' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $opportunity = Opportunity::query()->create($data);

        return new OpportunityResource($opportunity->load(['owner:id,name,email', 'account:id,name']));
    }

    public function show(Request $request, Opportunity $opportunity): OpportunityResource
    {
        abort_unless($request->user()->can('view', $opportunity), 403);

        return new OpportunityResource($opportunity->load(['owner:id,name,email', 'account:id,name']));
    }

    public function update(Request $request, Opportunity $opportunity): OpportunityResource
    {
        abort_unless($request->user()->can('update', $opportunity), 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'account_id' => ['sometimes', 'integer', 'exists:accounts,id'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'close_date' => ['sometimes', 'date'],
            'stage' => ['sometimes', 'string', Rule::in(array_keys(config('crm.opportunity_stages')))],
            'type' => ['nullable', 'string', Rule::in(config('crm.opportunity_types'))],
            'lead_source' => ['nullable', 'string', Rule::in(config('crm.lead_sources'))],
            'next_step' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $opportunity->update($data);

        return new OpportunityResource($opportunity->fresh()->load(['owner:id,name,email', 'account:id,name']));
    }

    public function destroy(Request $request, Opportunity $opportunity): Response
    {
        abort_unless($request->user()->can('delete', $opportunity), 403);
        $opportunity->delete();

        return response()->noContent();
    }
}
