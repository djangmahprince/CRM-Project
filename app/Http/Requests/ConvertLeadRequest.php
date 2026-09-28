<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lead = $this->route('lead');

        return $this->user()?->can('update', $lead) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'account_mode' => ['required', Rule::in(['create', 'existing'])],
            'account_id' => ['required_if:account_mode,existing', 'nullable', 'integer', 'exists:accounts,id'],
            'account_name' => ['required_if:account_mode,create', 'nullable', 'string', 'max:255'],
            'contact_mode' => ['required', Rule::in(['create', 'existing'])],
            'contact_id' => ['required_if:contact_mode,existing', 'nullable', 'integer', 'exists:contacts,id'],
            'create_opportunity' => ['sometimes', 'boolean'],
            'opportunity_name' => ['nullable', 'string', 'max:120'],
            'opportunity_amount' => ['nullable', 'numeric', 'min:0'],
            'opportunity_close_date' => ['nullable', 'date', 'after_or_equal:today'],
            'opportunity_stage' => ['nullable', 'string', Rule::in(array_keys(config('crm.opportunity_stages')))],
        ];
    }
}
