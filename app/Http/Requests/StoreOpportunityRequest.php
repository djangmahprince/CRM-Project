<?php

namespace App\Http\Requests;

use App\Models\Opportunity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Opportunity::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $closedStages = ['Closed Won', 'Closed Lost'];
        $stage = $this->string('stage')->toString();
        $isOpen = ! in_array($stage, $closedStages, true);

        return [
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
        ];
    }
}
