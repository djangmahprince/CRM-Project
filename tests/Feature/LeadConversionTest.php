<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Task;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class LeadConversionTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_lead_converts_atomically_to_account_contact_and_opportunity(): void
    {
        $user = $this->userWithRole('Sales Representative');
        $lead = Lead::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'company' => 'Convert Co',
            'last_name' => 'Rivera',
            'first_name' => 'Ana',
            'email' => 'ana@convert.test',
        ]);

        Task::factory()->create([
            'owner_id' => $user->id,
            'assigned_to_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'related_type' => 'lead',
            'related_id' => $lead->id,
            'status' => 'Not Started',
            'subject' => 'Follow up',
        ]);

        $this->actingAs($user)->post(route('leads.convert.store', $lead), [
            'account_mode' => 'create',
            'account_name' => 'Convert Co',
            'contact_mode' => 'create',
            'create_opportunity' => true,
            'opportunity_name' => 'New deal',
            'opportunity_amount' => 5000,
            'opportunity_close_date' => now()->addMonth()->toDateString(),
            'opportunity_stage' => 'Qualification',
        ])->assertRedirect();

        $lead->refresh();
        $this->assertTrue($lead->converted);
        $this->assertSame('Converted', $lead->lead_status);
        $this->assertNotNull($lead->converted_account_id);
        $this->assertNotNull($lead->converted_contact_id);
        $this->assertNotNull($lead->converted_opportunity_id);

        $this->assertDatabaseHas('tasks', [
            'subject' => 'Follow up',
            'related_type' => 'opportunity',
            'related_id' => $lead->converted_opportunity_id,
        ]);

        $this->actingAs($user)
            ->put(route('leads.update', $lead), [
                'last_name' => 'Nope',
                'company' => 'Convert Co',
                'lead_status' => 'Working',
            ])
            ->assertForbidden();
    }
}
