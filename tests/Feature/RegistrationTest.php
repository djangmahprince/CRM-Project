<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        config(['crm.registration_invite_code' => 'northstar-invite']);
    }

    public function test_guest_can_open_register_tab_via_redirect(): void
    {
        $this->get('/register')
            ->assertRedirect('/login?tab=register');
    }

    public function test_valid_invite_creates_sales_representative(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Rep',
            'email' => 'newrep@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'invitation_code' => 'northstar-invite',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::query()->where('email', 'newrep@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasRole('Sales Representative'));
        $this->assertFalse($user->hasRole('System Administrator'));
    }

    public function test_invalid_invite_code_is_rejected(): void
    {
        $response = $this->from('/login?tab=register')->post('/register', [
            'name' => 'New Rep',
            'email' => 'badinvite@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'invitation_code' => 'wrong-code',
        ]);

        $response->assertRedirect('/login?tab=register');
        $response->assertSessionHasErrors('invitation_code');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'badinvite@example.com']);
    }

    public function test_missing_invite_code_is_rejected(): void
    {
        $response = $this->from('/login?tab=register')->post('/register', [
            'name' => 'New Rep',
            'email' => 'missinginvite@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);

        $response->assertRedirect('/login?tab=register');
        $response->assertSessionHasErrors('invitation_code');
        $this->assertGuest();
    }

    public function test_empty_invite_env_rejects_registration(): void
    {
        config(['crm.registration_invite_code' => '']);

        $response = $this->from('/login?tab=register')->post('/register', [
            'name' => 'New Rep',
            'email' => 'emptyenv@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'invitation_code' => 'anything',
        ]);

        $response->assertSessionHasErrors('invitation_code');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'emptyenv@example.com']);
    }

    public function test_authenticated_users_cannot_register(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/register', [
                'name' => 'Another',
                'email' => 'another@example.com',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'invitation_code' => 'northstar-invite',
            ])
            ->assertRedirect('/dashboard');

        $this->assertDatabaseMissing('users', ['email' => 'another@example.com']);
    }

    public function test_registration_is_throttled(): void
    {
        RateLimiter::clear('register|'.request()->ip());

        for ($i = 0; $i < 5; $i++) {
            $this->post('/register', [
                'name' => "User {$i}",
                'email' => "throttle{$i}@example.com",
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'invitation_code' => 'wrong',
            ]);
        }

        $this->post('/register', [
            'name' => 'Blocked',
            'email' => 'blocked@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'invitation_code' => 'wrong',
        ])->assertStatus(429);
    }
}
