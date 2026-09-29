<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CaseResource;
use App\Models\CrmCase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CaseController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('viewAny', CrmCase::class), 403);

        $cases = CrmCase::query()
            ->visibleTo($request->user())
            ->with(['owner:id,name,email', 'account:id,name', 'contact:id,first_name,last_name'])
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 25), 100));

        return CaseResource::collection($cases);
    }

    public function store(Request $request): CaseResource
    {
        abort_unless($request->user()->can('create', CrmCase::class), 403);

        $data = $request->validate([
            'contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'subject' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(array_diff(config('crm.case_statuses'), ['Closed']))],
            'priority' => ['nullable', 'string', Rule::in(config('crm.priorities'))],
            'type' => ['nullable', 'string', Rule::in(config('crm.case_types'))],
            'origin' => ['required', 'string', Rule::in(config('crm.case_origins'))],
            'reason' => ['nullable', 'string', Rule::in(config('crm.case_reasons'))],
            'internal_comments' => ['nullable', 'string'],
            'web_email' => ['nullable', 'email', 'max:80'],
            'web_name' => ['nullable', 'string', 'max:80'],
            'web_company' => ['nullable', 'string', 'max:80'],
            'web_phone' => ['nullable', 'string', 'max:40'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $case = CrmCase::query()->create($data);

        return new CaseResource($case->load([
            'owner:id,name,email',
            'account:id,name',
            'contact:id,first_name,last_name',
        ]));
    }

    public function show(Request $request, CrmCase $case): CaseResource
    {
        abort_unless($request->user()->can('view', $case), 403);

        return new CaseResource($case->load([
            'owner:id,name,email',
            'account:id,name',
            'contact:id,first_name,last_name',
        ]));
    }

    public function update(Request $request, CrmCase $case): CaseResource
    {
        abort_unless($request->user()->can('update', $case), 403);

        $data = $request->validate([
            'contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'subject' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', Rule::in(array_diff(config('crm.case_statuses'), ['Closed']))],
            'priority' => ['nullable', 'string', Rule::in(config('crm.priorities'))],
            'type' => ['nullable', 'string', Rule::in(config('crm.case_types'))],
            'origin' => ['sometimes', 'string', Rule::in(config('crm.case_origins'))],
            'reason' => ['nullable', 'string', Rule::in(config('crm.case_reasons'))],
            'internal_comments' => ['nullable', 'string'],
            'web_email' => ['nullable', 'email', 'max:80'],
            'web_name' => ['nullable', 'string', 'max:80'],
            'web_company' => ['nullable', 'string', 'max:80'],
            'web_phone' => ['nullable', 'string', 'max:40'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $case->update($data);

        return new CaseResource($case->fresh()->load([
            'owner:id,name,email',
            'account:id,name',
            'contact:id,first_name,last_name',
        ]));
    }

    public function destroy(Request $request, CrmCase $case): Response
    {
        abort_unless($request->user()->can('delete', $case), 403);
        $case->delete();

        return response()->noContent();
    }
}
