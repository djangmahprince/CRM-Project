<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Opportunity;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class OpportunityCloneTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_opportunity_can_be_cloned(): void
    {
        $user = $this->userWithRole('Sales Representative');
        $account = Account::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $opportunity = Opportunity::factory()->create([
            'name' => 'Original Deal',
            'account_id' => $account->id,
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'stage' => 'Negotiation/Review',
            'amount' => 5000,
        ]);

        $response = $this->actingAs($user)->post(route('opportunities.clone', $opportunity));

        $clone = Opportunity::query()->where('name', 'Original Deal (Copy)')->firstOrFail();

        $response->assertRedirect(route('opportunities.edit', $clone));
        $this->assertSame('Qualification', $clone->stage);
        $this->assertSame(10, $clone->probability);
        $this->assertSame($account->id, $clone->account_id);
        $this->assertNotSame($opportunity->id, $clone->id);
    }

    public function test_stage_can_be_updated_via_stage_endpoint(): void
    {
        $user = $this->userWithRole('Sales Representative');
        $account = Account::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $opportunity = Opportunity::factory()->create([
            'account_id' => $account->id,
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'stage' => 'Qualification',
        ]);

        $this->actingAs($user)
            ->patch(route('opportunities.stage', $opportunity), ['stage' => 'Closed Won'])
            ->assertRedirect();

        $opportunity->refresh();
        $this->assertSame('Closed Won', $opportunity->stage);
        $this->assertTrue($opportunity->is_closed);
        $this->assertTrue($opportunity->is_won);
        $this->assertSame(100, $opportunity->probability);
    }
}
