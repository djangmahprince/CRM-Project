<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Rules\NotInPasswordHistory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = User::query()->where('email', $this->string('email')->toString())->first();

        $passwordRules = [
            'required',
            'confirmed',
            Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
        ];

        if ($user) {
            $passwordRules[] = new NotInPasswordHistory($user);
        }

        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => $passwordRules,
        ];
    }
}
