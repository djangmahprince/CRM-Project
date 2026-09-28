<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportSubscriptionRequest extends FormRequest
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
            'frequency' => ['required', Rule::in(['daily', 'weekly'])],
            'send_day' => ['nullable', 'integer', 'min:0', 'max:6'],
            'send_time' => ['nullable', 'date_format:H:i'],
        ];
    }
}
