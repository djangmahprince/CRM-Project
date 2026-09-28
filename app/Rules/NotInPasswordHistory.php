<?php

namespace App\Rules;

use App\Models\User;
use App\Support\PasswordHistoryService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotInPasswordHistory implements ValidationRule
{
    public function __construct(public User $user) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (app(PasswordHistoryService::class)->wasUsedRecently($this->user, $value)) {
            $fail('You cannot reuse one of your recent passwords.');
        }
    }
}
