<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class ApiLeadTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_api_can_issue_token_and_list_leads(): void
    {
        $user = $this->userWithRole('Sales Representative', [
            'email' => 'api@example.com',
            'password' => 'password',
        ]);

        Lead::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'last_name' => 'ApiLead',
        ]);

        $tokenResponse = $this->postJson('/api/tokens', [
            'email' => 'api@example.com',
            'password' => 'password',
            'device_name' => 'phpunit',
        ])->assertSuccessful()
            ->assertJsonStructure(['token', 'token_type']);

        $token = $tokenResponse->json('token');

        $this->withToken($token)
            ->getJson('/api/v1/leads')
            ->assertSuccessful()
            ->assertJsonPath('data.0.last_name', 'ApiLead');

        Sanctum::actingAs($user);
        $this->postJson('/api/v1/leads', [
            'last_name' => 'Created',
            'company' => 'API Co',
            'lead_status' => 'New',
        ])->assertSuccessful()
            ->assertJsonPath('data.last_name', 'Created');
    }
}
