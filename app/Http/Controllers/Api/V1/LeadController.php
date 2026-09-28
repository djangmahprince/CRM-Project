<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeadResource;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $leads = Lead::query()
            ->visibleTo($request->user())
            ->with('owner:id,name,email')
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 25), 100));

        return LeadResource::collection($leads);
    }

    public function store(Request $request): LeadResource
    {
        $data = $request->validate([
            'last_name' => ['required', 'string', 'max:80'],
            'first_name' => ['nullable', 'string', 'max:40'],
            'company' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:80'],
            'lead_status' => ['required', Rule::in(config('crm.lead_statuses'))],
            'lead_source' => ['nullable', 'string', 'max:80'],
        ]);

        $lead = Lead::query()->create($data);

        return new LeadResource($lead->load('owner:id,name,email'));
    }

    public function show(Request $request, Lead $lead): LeadResource
    {
        abort_unless($request->user()->can('view', $lead), 403);

        return new LeadResource($lead->load('owner:id,name,email'));
    }

    public function update(Request $request, Lead $lead): LeadResource
    {
        abort_unless($request->user()->can('update', $lead), 403);

        $data = $request->validate([
            'last_name' => ['sometimes', 'string', 'max:80'],
            'first_name' => ['nullable', 'string', 'max:40'],
            'company' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:80'],
            'lead_status' => ['sometimes', Rule::in(array_values(array_diff(config('crm.lead_statuses'), ['Converted'])))],
            'lead_source' => ['nullable', 'string', 'max:80'],
        ]);

        $lead->update($data);

        return new LeadResource($lead->fresh()->load('owner:id,name,email'));
    }

    public function destroy(Request $request, Lead $lead): Response
    {
        abort_unless($request->user()->can('delete', $lead), 403);
        $lead->delete();

        return response()->noContent();
    }
}
