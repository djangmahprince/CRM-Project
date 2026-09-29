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

    public function test_api_cannot_create_lead_as_converted(): void
    {
        $user = $this->userWithRole('Sales Representative');
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/leads', [
            'last_name' => 'Bad',
            'company' => 'API Co',
            'lead_status' => 'Converted',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['lead_status']);
    }

    public function test_read_only_user_cannot_create_lead_via_api(): void
    {
        $user = $this->userWithRole('Read-Only User');
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/leads', [
            'last_name' => 'Nope',
            'company' => 'API Co',
            'lead_status' => 'New',
        ])->assertForbidden();
    }
}
