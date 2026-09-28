<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportDefinitionRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'folder' => ['nullable', 'string', 'max:120'],
            'report_type' => ['required', Rule::in(['leads', 'accounts', 'contacts', 'opportunities', 'cases'])],
            'definition' => ['required', 'array'],
            'definition.columns' => ['required', 'array', 'min:1'],
            'definition.columns.*' => ['string'],
            'definition.filters' => ['nullable', 'array'],
            'definition.group_by' => ['nullable', 'string'],
            'definition.chart' => ['nullable', 'string', Rule::in(['none', 'bar', 'pie'])],
        ];
    }
}
