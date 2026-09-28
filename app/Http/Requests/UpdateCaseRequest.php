<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('case')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
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
        ];
    }
}
