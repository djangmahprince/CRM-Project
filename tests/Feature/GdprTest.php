<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class GdprTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_admin_can_export_and_anonymize_user(): void
    {
        $admin = $this->userWithRole('System Administrator', ['email' => 'admin@example.com']);
        $target = $this->userWithRole('Sales Representative', [
            'name' => 'Target User',
            'email' => 'target@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('gdpr.export', $target))
            ->assertSuccessful()
            ->assertHeader('content-disposition');

        $this->actingAs($admin)
            ->delete(route('gdpr.destroy', $target))
            ->assertRedirect();

        $target->refresh();
        $this->assertStringStartsWith('Deleted User', $target->name);
        $this->assertStringStartsWith('deleted+', $target->email);
    }

    public function test_non_admin_cannot_access_gdpr(): void
    {
        $user = $this->userWithRole('Sales Representative');
        $other = User::factory()->create();

        $this->actingAs($user)
            ->get(route('gdpr.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('gdpr.export', $other))
            ->assertForbidden();
    }
}
