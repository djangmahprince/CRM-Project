<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Contact;
use App\Models\OrgSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class ApiContactTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_api_requires_authentication_for_contacts(): void
    {
        $this->getJson('/api/v1/contacts')->assertUnauthorized();
    }

    public function test_api_can_list_create_and_show_contact(): void
    {
        $user = $this->userWithRole('Sales Representative', [
            'email' => 'api-contacts@example.com',
            'password' => 'password',
        ]);

        $account = Account::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Contact::factory()->create([
            'account_id' => $account->id,
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'last_name' => 'ApiContact',
        ]);

        $tokenResponse = $this->postJson('/api/tokens', [
            'email' => 'api-contacts@example.com',
            'password' => 'password',
            'device_name' => 'phpunit',
        ])->assertSuccessful();

        $this->withToken($tokenResponse->json('token'))
            ->getJson('/api/v1/contacts')
            ->assertSuccessful()
            ->assertJsonPath('data.0.last_name', 'ApiContact');

        Sanctum::actingAs($user);

        $create = $this->postJson('/api/v1/contacts', [
            'account_id' => $account->id,
            'last_name' => 'Created',
            'first_name' => 'API',
        ])->assertSuccessful()
            ->assertJsonPath('data.last_name', 'Created');

        $id = $create->json('data.id');

        $this->getJson("/api/v1/contacts/{$id}")
            ->assertSuccessful()
            ->assertJsonPath('data.last_name', 'Created')
            ->assertJsonPath('data.account.id', $account->id);
    }

    public function test_api_contact_list_respects_visibility_scope(): void
    {
        OrgSetting::put('default_sharing', 'private');

        $owner = $this->userWithRole('Sales Representative', ['email' => 'contact-owner@example.com']);
        $viewer = $this->userWithRole('Sales Representative', ['email' => 'contact-viewer@example.com']);

        $account = Account::factory()->create([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
        ]);

        Contact::factory()->create([
            'account_id' => $account->id,
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
            'last_name' => 'Hidden',
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson('/api/v1/contacts')
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    }
}
