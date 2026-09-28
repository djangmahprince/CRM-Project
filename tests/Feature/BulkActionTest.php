<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Notifications\OwnershipChangedNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class BulkActionTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_user_can_bulk_update_lead_owner_and_status(): void
    {
        Notification::fake();

        $user = $this->userWithRole('Sales Representative', ['email' => 'bulk-owner@example.com']);
        $newOwner = $this->userWithRole('Sales Representative', ['email' => 'new-owner@example.com']);

        $lead = Lead::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'lead_status' => 'New',
            'last_name' => 'Bulk',
        ]);

        $this->actingAs($user)->post('/bulk-actions', [
            'object' => 'leads',
            'ids' => [$lead->id],
            'action' => 'status',
            'status' => 'Working',
        ])->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'lead_status' => 'Working',
        ]);

        $this->actingAs($user)->post('/bulk-actions', [
            'object' => 'leads',
            'ids' => [$lead->id],
            'action' => 'owner',
            'owner_id' => $newOwner->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'owner_id' => $newOwner->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ownership_changed',
            'auditable_type' => 'lead',
            'auditable_id' => $lead->id,
        ]);

        Notification::assertSentTo($user, OwnershipChangedNotification::class);
        Notification::assertSentTo($newOwner, OwnershipChangedNotification::class);
    }

    public function test_user_can_bulk_delete_leads(): void
    {
        $user = $this->userWithRole('Sales Representative');

        $lead = Lead::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->actingAs($user)->post('/bulk-actions', [
            'object' => 'leads',
            'ids' => [$lead->id],
            'action' => 'delete',
        ])->assertRedirect();

        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
    }
}
