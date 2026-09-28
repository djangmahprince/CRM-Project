<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSavedSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'object_type' => ['required', Rule::in(['leads', 'accounts', 'contacts', 'opportunities', 'cases'])],
            'logic' => ['required', Rule::in(['and', 'or'])],
            'conditions' => ['required', 'array', 'min:1'],
            'conditions.*.field' => ['required', 'string', 'max:80'],
            'conditions.*.operator' => ['required', Rule::in(['contains', 'equals', 'not_equals', 'starts_with', 'ends_with', 'gt', 'lt'])],
            'conditions.*.value' => ['required'],
        ];
    }
}
