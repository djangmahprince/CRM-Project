<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Opportunity;
use App\Models\OrgSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class ApiOpportunityTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_api_requires_authentication_for_opportunities(): void
    {
        $this->getJson('/api/v1/opportunities')->assertUnauthorized();
    }

    public function test_api_can_list_create_and_show_opportunity(): void
    {
        $user = $this->userWithRole('Sales Representative', [
            'email' => 'api-opps@example.com',
            'password' => 'password',
        ]);

        $account = Account::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Opportunity::factory()->create([
            'account_id' => $account->id,
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'name' => 'Owned Deal',
            'stage' => 'Qualification',
            'close_date' => now()->addMonth()->toDateString(),
        ]);

        $tokenResponse = $this->postJson('/api/tokens', [
            'email' => 'api-opps@example.com',
            'password' => 'password',
            'device_name' => 'phpunit',
        ])->assertSuccessful();

        $this->withToken($tokenResponse->json('token'))
            ->getJson('/api/v1/opportunities')
            ->assertSuccessful()
            ->assertJsonPath('data.0.name', 'Owned Deal');

        Sanctum::actingAs($user);

        $create = $this->postJson('/api/v1/opportunities', [
            'name' => 'API Deal',
            'account_id' => $account->id,
            'stage' => 'Qualification',
            'close_date' => now()->addWeeks(2)->toDateString(),
            'amount' => 15000,
        ])->assertSuccessful()
            ->assertJsonPath('data.name', 'API Deal')
            ->assertJsonPath('data.probability', 10);

        $id = $create->json('data.id');

        $this->getJson("/api/v1/opportunities/{$id}")
            ->assertSuccessful()
            ->assertJsonPath('data.name', 'API Deal')
            ->assertJsonPath('data.account.id', $account->id);
    }

    public function test_api_opportunity_list_respects_visibility_scope(): void
    {
        OrgSetting::put('default_sharing', 'private');

        $owner = $this->userWithRole('Sales Representative', ['email' => 'opp-owner@example.com']);
        $viewer = $this->userWithRole('Sales Representative', ['email' => 'opp-viewer@example.com']);

        $account = Account::factory()->create([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
        ]);

        Opportunity::factory()->create([
            'account_id' => $account->id,
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
            'name' => 'Private Deal',
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson('/api/v1/opportunities')
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    }
}
