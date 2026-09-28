<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\OrgSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class LeadVisibilityTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_private_lead_is_hidden_from_other_sales_reps(): void
    {
        OrgSetting::put('default_sharing', 'private');

        $owner = $this->userWithRole('Sales Representative', ['email' => 'owner@example.com']);
        $viewer = $this->userWithRole('Sales Representative', ['email' => 'viewer@example.com']);

        $lead = Lead::factory()->create([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
        ]);

        $this->actingAs($viewer)
            ->get(route('leads.show', $lead))
            ->assertForbidden();
    }

    public function test_administrator_can_view_private_lead(): void
    {
        OrgSetting::put('default_sharing', 'private');

        $owner = $this->userWithRole('Sales Representative', ['email' => 'owner@example.com']);
        $admin = $this->userWithRole('System Administrator', ['email' => 'admin@example.com']);

        $lead = Lead::factory()->create([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
        ]);

        $this->actingAs($admin)
            ->get(route('leads.show', $lead))
            ->assertSuccessful();
    }
}
