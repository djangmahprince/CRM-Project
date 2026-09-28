<?php

namespace App\Http\Requests;

use App\Support\CrmRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('event')) ?? false;
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
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'all_day' => ['sometimes', 'boolean'],
            'location' => ['nullable', 'string', 'max:255'],
            'show_as' => ['required', 'string', Rule::in(config('crm.show_as'))],
            'is_private' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}
