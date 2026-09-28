<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class MfaTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_user_can_enable_mfa_and_is_challenged_on_login(): void
    {
        $user = $this->userWithRole('Sales Representative', [
            'email' => 'mfa@example.com',
            'password' => 'password',
        ]);

        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();

        $this->actingAs($user)
            ->withSession(['mfa_setup_secret' => $secret])
            ->post('/settings/mfa', [
                'code' => $google2fa->getCurrentOtp($secret),
            ])
            ->assertRedirect(route('mfa.edit'));

        $user->refresh();
        $this->assertTrue($user->mfa_enabled);

        auth()->logout();

        $this->post('/login', [
            'email' => 'mfa@example.com',
            'password' => 'password',
        ])->assertRedirect(route('mfa.challenge'));

        $this->assertGuest();
        $this->assertTrue(session()->has('mfa_pending_user_id'));

        $this->post('/mfa/challenge', [
            'code' => $google2fa->getCurrentOtp(Crypt::decryptString($user->mfa_secret)),
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }
}
