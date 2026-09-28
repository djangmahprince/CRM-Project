<?php

namespace App\Http\Requests;

use App\Models\Task;
use App\Support\CrmRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Task::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'assigned_to_id' => ['required', 'integer', 'exists:users,id'],
            'related_type' => ['nullable', 'string', Rule::in(array_keys(CrmRegistry::morphMap()))],
            'related_id' => ['nullable', 'integer', 'required_with:related_type'],
            'contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'string', Rule::in(config('crm.task_statuses'))],
            'priority' => ['required', 'string', Rule::in(config('crm.task_priorities'))],
            'comments' => ['nullable', 'string'],
            'reminder_set' => ['sometimes', 'boolean'],
            'reminder_at' => ['nullable', 'date'],
        ];
    }
}
