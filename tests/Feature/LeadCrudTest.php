<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class LeadCrudTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_sales_representative_can_create_and_view_a_lead(): void
    {
        $user = $this->userWithRole('Sales Representative');

        $response = $this->actingAs($user)->post('/leads', [
            'last_name' => 'Chen',
            'company' => 'Acme Logistics',
            'lead_status' => 'New',
            'email' => 'chen@example.com',
        ]);

        $lead = Lead::query()->firstOrFail();

        $response->assertRedirect(route('leads.show', $lead));
        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'last_name' => 'Chen',
            'company' => 'Acme Logistics',
            'owner_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('leads.show', $lead))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Leads/Show')
                ->where('lead.last_name', 'Chen')
            );
    }

    public function test_lead_list_is_scoped_to_visible_records(): void
    {
        $owner = $this->userWithRole('Sales Representative', ['email' => 'owner@example.com']);
        $other = $this->userWithRole('Sales Representative', ['email' => 'other@example.com']);

        $visible = Lead::factory()->create([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
            'last_name' => 'Visible',
        ]);

        Lead::factory()->create([
            'owner_id' => $other->id,
            'created_by' => $other->id,
            'updated_by' => $other->id,
            'last_name' => 'Hidden',
        ]);

        $this->actingAs($owner)
            ->get('/leads')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Leads/Index')
                ->has('leads.data', 1)
                ->where('leads.data.0.id', $visible->id)
            );
    }

    public function test_converted_lead_cannot_be_updated(): void
    {
        $user = $this->userWithRole('Sales Representative');

        $lead = Lead::factory()->converted()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->put(route('leads.update', $lead), [
                'last_name' => 'Changed',
                'company' => $lead->company,
                'lead_status' => 'Working',
            ])
            ->assertForbidden();
    }

    public function test_converted_lead_cannot_be_deleted(): void
    {
        $user = $this->userWithRole('Sales Representative');

        $lead = Lead::factory()->converted()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->delete(route('leads.destroy', $lead))
            ->assertForbidden();

        $this->assertDatabaseHas('leads', ['id' => $lead->id, 'deleted_at' => null]);
    }

    public function test_validation_requires_last_name_and_company(): void
    {
        $user = $this->userWithRole('Sales Representative');

        $this->actingAs($user)
            ->from('/leads/create')
            ->post('/leads', [
                'lead_status' => 'New',
            ])
            ->assertRedirect('/leads/create')
            ->assertSessionHasErrors(['last_name', 'company']);
    }
}
