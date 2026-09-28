<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('contact')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
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
            'reports_to_id' => ['nullable', 'integer', 'exists:contacts,id', Rule::notIn([(int) $this->route('contact')?->id])],
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
        ];
    }
}
