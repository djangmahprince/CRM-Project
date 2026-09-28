<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_forgot_password_page_renders(): void
    {
        $this->get('/forgot-password')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('Auth/ForgotPassword'));
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHasNoErrors();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();

        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPass1!',
            'password_confirmation' => 'NewPass1!',
        ]);

        $response->assertRedirect('/login');
        $this->assertTrue(auth()->attempt([
            'email' => $user->email,
            'password' => 'NewPass1!',
        ]));
    }
}
