<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\OrgSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class ApiAccountTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_api_requires_authentication_for_accounts(): void
    {
        $this->getJson('/api/v1/accounts')->assertUnauthorized();
    }

    public function test_api_can_list_create_and_show_account(): void
    {
        $user = $this->userWithRole('Sales Representative', [
            'email' => 'api-accounts@example.com',
            'password' => 'password',
        ]);

        Account::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'name' => 'Owned Account',
        ]);

        $tokenResponse = $this->postJson('/api/tokens', [
            'email' => 'api-accounts@example.com',
            'password' => 'password',
            'device_name' => 'phpunit',
        ])->assertSuccessful();

        $this->withToken($tokenResponse->json('token'))
            ->getJson('/api/v1/accounts')
            ->assertSuccessful()
            ->assertJsonPath('data.0.name', 'Owned Account');

        Sanctum::actingAs($user);

        $create = $this->postJson('/api/v1/accounts', [
            'name' => 'API Account Co',
            'type' => 'Customer',
        ])->assertSuccessful()
            ->assertJsonPath('data.name', 'API Account Co');

        $id = $create->json('data.id');

        $this->getJson("/api/v1/accounts/{$id}")
            ->assertSuccessful()
            ->assertJsonPath('data.name', 'API Account Co');
    }

    public function test_api_account_list_respects_visibility_scope(): void
    {
        OrgSetting::put('default_sharing', 'private');

        $owner = $this->userWithRole('Sales Representative', ['email' => 'acct-owner@example.com']);
        $viewer = $this->userWithRole('Sales Representative', ['email' => 'acct-viewer@example.com']);

        Account::factory()->create([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
            'name' => 'Private Account',
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson('/api/v1/accounts')
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    }
}
