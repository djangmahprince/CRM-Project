<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $user = User::query()->where('email', $this->string('email')->toString())->first();

        if ($user?->isLocked()) {
            throw ValidationException::withMessages([
                'email' => __('Your account is locked. Try again after :minutes minutes.', [
                    'minutes' => (int) config('crm.lockout_minutes'),
                ]),
            ]);
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            $this->recordFailedAttempt($user);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        /** @var User $authenticated */
        $authenticated = Auth::user();
        $authenticated->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->save();

        $this->session()->regenerate();
        $this->session()->put('last_activity_at', now()->timestamp);
    }

    private function recordFailedAttempt(?User $user): void
    {
        if (! $user) {
            return;
        }

        $attempts = $user->failed_login_attempts + 1;
        $maxAttempts = (int) config('crm.lockout_attempts');

        $attributes = ['failed_login_attempts' => $attempts];

        if ($attempts >= $maxAttempts) {
            $attributes['locked_until'] = now()->addMinutes((int) config('crm.lockout_minutes'));
            $attributes['failed_login_attempts'] = 0;
            event(new Lockout($this));
        }

        $user->forceFill($attributes)->save();
    }
}
