<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\OrgSetting;
use App\Models\RecordShare;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class RecordShareTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_shared_private_lead_is_visible_to_grantee(): void
    {
        OrgSetting::put('default_sharing', 'private');

        $owner = $this->userWithRole('Sales Representative', ['email' => 'share-owner@example.com']);
        $grantee = $this->userWithRole('Sales Representative', ['email' => 'share-grantee@example.com']);

        $lead = Lead::factory()->create([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
            'last_name' => 'Shared',
        ]);

        $this->actingAs($grantee)
            ->get(route('leads.show', $lead))
            ->assertForbidden();

        RecordShare::query()->create([
            'shareable_type' => 'lead',
            'shareable_id' => $lead->id,
            'user_id' => $grantee->id,
            'access' => 'read',
        ]);

        $this->actingAs($grantee)
            ->get(route('leads.show', $lead))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Leads/Show')
                ->where('lead.last_name', 'Shared')
            );

        $this->actingAs($grantee)
            ->get('/leads')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Leads/Index')
                ->has('leads.data', 1)
            );
    }
}
