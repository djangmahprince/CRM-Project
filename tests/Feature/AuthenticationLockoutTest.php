<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthenticationLockoutTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_account_locks_after_configured_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'agent@example.com',
            'password' => 'password',
        ]);

        $attempts = (int) config('crm.lockout_attempts');

        for ($i = 0; $i < $attempts; $i++) {
            $this->from('/login')->post('/login', [
                'email' => 'agent@example.com',
                'password' => 'wrong-password',
            ])->assertRedirect('/login');
        }

        $user->refresh();

        $this->assertTrue($user->isLocked());

        $this->from('/login')->post('/login', [
            'email' => 'agent@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
