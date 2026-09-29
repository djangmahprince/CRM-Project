<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Task;
use App\Models\WorkflowRule;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class WorkflowAutomationTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_matching_lead_status_rule_creates_task_for_owner(): void
    {
        $owner = $this->userWithRole('Sales Representative', ['email' => 'wf-owner@example.com']);
        $admin = $this->userWithRole('System Administrator', ['email' => 'wf-admin@example.com']);

        WorkflowRule::factory()->create([
            'name' => 'Qualified follow-up',
            'object_type' => 'lead',
            'field' => 'lead_status',
            'operator' => 'equals',
            'value' => 'Qualified',
            'action_type' => 'create_task',
            'action_config' => [
                'subject_template' => 'Follow up with {{name}}',
                'assign_to' => 'owner',
            ],
            'enabled' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $lead = Lead::factory()->create([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'lead_status' => 'Working',
        ]);

        $this->actingAs($owner)->put(route('leads.update', $lead), [
            'last_name' => $lead->last_name,
            'company' => $lead->company,
            'lead_status' => 'Qualified',
            'email' => $lead->email,
        ])->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'related_type' => 'lead',
            'related_id' => $lead->id,
            'assigned_to_id' => $owner->id,
            'subject' => 'Follow up with Ada Lovelace',
        ]);
        $this->assertSame(1, Task::query()->count());
    }

    public function test_non_matching_update_does_not_create_task(): void
    {
        $owner = $this->userWithRole('Sales Representative', ['email' => 'wf-owner2@example.com']);
        $admin = $this->userWithRole('System Administrator', ['email' => 'wf-admin2@example.com']);

        WorkflowRule::factory()->create([
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
            'value' => 'Qualified',
        ]);

        $lead = Lead::factory()->create([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
            'lead_status' => 'New',
        ]);

        $this->actingAs($owner)->put(route('leads.update', $lead), [
            'last_name' => $lead->last_name,
            'company' => $lead->company,
            'lead_status' => 'Working',
            'email' => $lead->email,
        ])->assertRedirect();

        $this->assertSame(0, Task::query()->count());
    }

    public function test_disabled_rule_does_not_create_task(): void
    {
        $owner = $this->userWithRole('Sales Representative', ['email' => 'wf-owner3@example.com']);
        $admin = $this->userWithRole('System Administrator', ['email' => 'wf-admin3@example.com']);

        WorkflowRule::factory()->disabled()->create([
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
            'value' => 'Qualified',
        ]);

        $lead = Lead::factory()->create([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
            'lead_status' => 'Working',
        ]);

        $this->actingAs($owner)->put(route('leads.update', $lead), [
            'last_name' => $lead->last_name,
            'company' => $lead->company,
            'lead_status' => 'Qualified',
            'email' => $lead->email,
        ])->assertRedirect();

        $this->assertSame(0, Task::query()->count());
    }

    public function test_opportunity_stage_rule_creates_task(): void
    {
        $owner = $this->userWithRole('Sales Representative', ['email' => 'wf-opp-owner@example.com']);
        $admin = $this->userWithRole('Sales Manager', ['email' => 'wf-manager@example.com']);

        WorkflowRule::factory()->create([
            'name' => 'Proposal task',
            'object_type' => 'opportunity',
            'field' => 'stage',
            'operator' => 'equals',
            'value' => 'Proposal/Price Quote',
            'action_config' => [
                'subject_template' => 'Prepare quote for {{name}}',
                'assign_to' => 'owner',
            ],
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $opportunity = Opportunity::factory()->create([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
            'name' => 'Big Deal',
            'stage' => 'Qualification',
            'close_date' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($owner)->put(route('opportunities.update', $opportunity), [
            'name' => $opportunity->name,
            'account_id' => $opportunity->account_id,
            'amount' => $opportunity->amount,
            'close_date' => $opportunity->close_date->toDateString(),
            'stage' => 'Proposal/Price Quote',
        ])->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'related_type' => 'opportunity',
            'related_id' => $opportunity->id,
            'assigned_to_id' => $owner->id,
            'subject' => 'Prepare quote for Big Deal',
        ]);
    }

    public function test_admin_can_create_and_delete_workflow_rules(): void
    {
        $admin = $this->userWithRole('System Administrator', ['email' => 'wf-ui@example.com']);

        $this->actingAs($admin)
            ->get(route('workflows.index'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('Admin/Workflows'));

        $this->actingAs($admin)->post(route('workflows.store'), [
            'name' => 'Nurture task',
            'object_type' => 'lead',
            'field' => 'lead_status',
            'operator' => 'equals',
            'value' => 'Nurturing',
            'action_type' => 'create_task',
            'subject_template' => 'Nurture {{name}}',
            'assign_to' => 'owner',
            'enabled' => true,
        ])->assertRedirect(route('workflows.index'));

        $rule = WorkflowRule::query()->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('workflows.destroy', $rule))
            ->assertRedirect(route('workflows.index'));

        $this->assertDatabaseMissing('workflow_rules', ['id' => $rule->id]);
    }

    public function test_sales_rep_cannot_manage_workflows(): void
    {
        $rep = $this->userWithRole('Sales Representative', ['email' => 'wf-rep@example.com']);

        $this->actingAs($rep)
            ->get(route('workflows.index'))
            ->assertForbidden();
    }
}
