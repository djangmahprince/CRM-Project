<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CrmCase;
use App\Models\OrgSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class ApiCaseTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_api_requires_authentication_for_cases(): void
    {
        $this->getJson('/api/v1/cases')->assertUnauthorized();
    }

    public function test_api_can_list_create_and_show_case(): void
    {
        $user = $this->userWithRole('Service Representative', [
            'email' => 'api-cases@example.com',
            'password' => 'password',
        ]);

        $account = Account::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        CrmCase::factory()->create([
            'account_id' => $account->id,
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'subject' => 'Owned Case',
            'status' => 'New',
            'origin' => 'Phone',
        ]);

        $tokenResponse = $this->postJson('/api/tokens', [
            'email' => 'api-cases@example.com',
            'password' => 'password',
            'device_name' => 'phpunit',
        ])->assertSuccessful();

        $this->withToken($tokenResponse->json('token'))
            ->getJson('/api/v1/cases')
            ->assertSuccessful()
            ->assertJsonPath('data.0.subject', 'Owned Case');

        Sanctum::actingAs($user);

        $create = $this->postJson('/api/v1/cases', [
            'account_id' => $account->id,
            'subject' => 'API Case',
            'status' => 'New',
            'origin' => 'Email',
            'priority' => 'Medium',
        ])->assertSuccessful()
            ->assertJsonPath('data.subject', 'API Case');

        $id = $create->json('data.id');
        $caseNumber = $create->json('data.case_number');

        $this->assertNotEmpty($caseNumber);
        $this->assertStringStartsWith('CAS-', $caseNumber);

        $this->getJson("/api/v1/cases/{$id}")
            ->assertSuccessful()
            ->assertJsonPath('data.subject', 'API Case')
            ->assertJsonPath('data.account.id', $account->id);
    }

    public function test_api_case_list_respects_visibility_scope(): void
    {
        OrgSetting::put('default_sharing', 'private');

        $owner = $this->userWithRole('Service Representative', ['email' => 'case-owner@example.com']);
        $viewer = $this->userWithRole('Service Representative', ['email' => 'case-viewer@example.com']);

        $account = Account::factory()->create([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
        ]);

        CrmCase::factory()->create([
            'account_id' => $account->id,
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
            'subject' => 'Private Case',
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson('/api/v1/cases')
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    }

    public function test_sales_rep_cannot_create_case_via_api(): void
    {
        $user = $this->userWithRole('Sales Representative', [
            'email' => 'sales-no-case@example.com',
            'password' => 'password',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/cases', [
            'subject' => 'Should Fail',
            'status' => 'New',
            'origin' => 'Phone',
        ])->assertForbidden();
    }
}
