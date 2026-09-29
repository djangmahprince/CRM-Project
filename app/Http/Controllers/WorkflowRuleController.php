<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkflowRuleRequest;
use App\Models\WorkflowRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class WorkflowRuleController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', WorkflowRule::class);

        return Inertia::render('Admin/Workflows', [
            'rules' => WorkflowRule::query()
                ->with('creator:id,name,email')
                ->latest()
                ->get()
                ->map(fn (WorkflowRule $rule) => [
                    'id' => $rule->id,
                    'name' => $rule->name,
                    'object_type' => $rule->object_type,
                    'field' => $rule->field,
                    'operator' => $rule->operator,
                    'value' => $rule->value,
                    'action_type' => $rule->action_type,
                    'subject_template' => $rule->action_config['subject_template'] ?? '',
                    'assign_to' => $rule->action_config['assign_to'] ?? 'owner',
                    'enabled' => $rule->enabled,
                    'creator' => $rule->creator?->only(['id', 'name', 'email']),
                    'created_at' => $rule->created_at?->toDateTimeString(),
                ]),
            'options' => [
                'object_types' => ['lead', 'opportunity'],
                'operators' => ['equals', 'not_equals'],
                'action_types' => ['create_task'],
                'lead_fields' => ['lead_status', 'lead_source', 'rating'],
                'opportunity_fields' => ['stage', 'lead_source', 'type'],
                'lead_statuses' => config('crm.lead_statuses'),
                'opportunity_stages' => array_keys(config('crm.opportunity_stages')),
                'lead_sources' => config('crm.lead_sources'),
                'ratings' => config('crm.ratings'),
                'opportunity_types' => config('crm.opportunity_types'),
            ],
            'can' => [
                'create' => $request->user()->can('create', WorkflowRule::class),
                'manage' => $request->user()->can('viewAny', WorkflowRule::class),
            ],
        ]);
    }

    public function store(StoreWorkflowRuleRequest $request): RedirectResponse
    {
        WorkflowRule::query()->create($request->workflowAttributes());

        return redirect()
            ->route('workflows.index')
            ->with('success', 'Workflow rule created.');
    }

    public function destroy(Request $request, WorkflowRule $workflow): RedirectResponse
    {
        Gate::authorize('delete', $workflow);
        $workflow->delete();

        return redirect()
            ->route('workflows.index')
            ->with('success', 'Workflow rule deleted.');
    }
}
