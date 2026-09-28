<?php

namespace App\Http\Requests;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Lead::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'salutation' => ['nullable', 'string', 'max:20', Rule::in(config('crm.salutations'))],
            'first_name' => ['nullable', 'string', 'max:40'],
            'last_name' => ['required', 'string', 'max:80'],
            'company' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:128'],
            'email' => ['nullable', 'email', 'max:80'],
            'phone' => ['nullable', 'string', 'max:40'],
            'mobile' => ['nullable', 'string', 'max:40'],
            'lead_status' => ['required', 'string', Rule::in(array_diff(config('crm.lead_statuses'), ['Converted']))],
            'lead_source' => ['nullable', 'string', Rule::in(config('crm.lead_sources'))],
            'rating' => ['nullable', 'string', Rule::in(config('crm.ratings'))],
            'industry' => ['nullable', 'string', Rule::in(config('crm.industries'))],
            'annual_revenue' => ['nullable', 'numeric', 'min:0'],
            'number_of_employees' => ['nullable', 'integer', 'min:0'],
            'website' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:80'],
            'state' => ['nullable', 'string', 'max:80'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
