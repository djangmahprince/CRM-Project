<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDashboardRequest extends FormRequest
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
            'description' => ['nullable', 'string', 'max:500'],
            'folder' => ['nullable', 'string', 'max:120'],
            'auto_refresh_minutes' => ['nullable', 'integer', 'min:1', 'max:60'],
            'widgets' => ['nullable', 'array'],
            'widgets.*.title' => ['required_with:widgets', 'string', 'max:120'],
            'widgets.*.type' => ['nullable', Rule::in(['table', 'metric', 'chart'])],
            'widgets.*.report_id' => ['nullable', 'integer', 'exists:reports,id'],
            'widgets.*.x' => ['nullable', 'integer', 'min:0', 'max:11'],
            'widgets.*.y' => ['nullable', 'integer', 'min:0', 'max:20'],
            'widgets.*.w' => ['nullable', 'integer', 'min:1', 'max:12'],
            'widgets.*.h' => ['nullable', 'integer', 'min:1', 'max:12'],
            'widgets.*.options' => ['nullable', 'array'],
        ];
    }
}
