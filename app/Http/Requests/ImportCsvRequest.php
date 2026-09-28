<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class ImportCsvRequest extends FormRequest
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
            'object' => ['required', Rule::in(['leads', 'accounts', 'contacts'])],
            'file' => ['required', File::types(['csv', 'txt'])->max(10240)],
            'mapping' => ['required', 'array'],
            'mapping.*' => ['nullable', 'string'],
        ];
    }
}
