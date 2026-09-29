<?php

namespace App\Http\Requests;

use App\Models\WorkflowRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkflowRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', WorkflowRule::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $objectType = $this->string('object_type')->toString();
        $fields = match ($objectType) {
            'lead' => ['lead_status', 'lead_source', 'rating'],
            'opportunity' => ['stage', 'lead_source', 'type'],
            default => [],
        };

        return [
            'name' => ['required', 'string', 'max:120'],
            'object_type' => ['required', 'string', Rule::in(['lead', 'opportunity'])],
            'field' => ['required', 'string', Rule::in($fields)],
            'operator' => ['required', 'string', Rule::in(['equals', 'not_equals'])],
            'value' => ['required', 'string', 'max:255'],
            'action_type' => ['required', 'string', Rule::in(['create_task'])],
            'subject_template' => ['required', 'string', 'max:255'],
            'assign_to' => ['required', 'string', Rule::in(['owner'])],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function workflowAttributes(): array
    {
        $validated = $this->validated();

        return [
            'name' => $validated['name'],
            'object_type' => $validated['object_type'],
            'field' => $validated['field'],
            'operator' => $validated['operator'],
            'value' => $validated['value'],
            'action_type' => $validated['action_type'],
            'action_config' => [
                'subject_template' => $validated['subject_template'],
                'assign_to' => $validated['assign_to'],
            ],
            'enabled' => $this->boolean('enabled', true),
            'created_by' => $this->user()?->id,
            'updated_by' => $this->user()?->id,
        ];
    }
}
