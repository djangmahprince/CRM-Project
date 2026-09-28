<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreAttachmentRequest extends FormRequest
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
        $mimes = implode(',', config('crm.attachment_mimes'));
        $maxKb = (int) config('crm.attachment_max_kb');

        return [
            'attachable_type' => ['required', 'string'],
            'attachable_id' => ['required', 'integer'],
            'file' => ['required', File::types(explode(',', $mimes))->max($maxKb)],
        ];
    }
}
