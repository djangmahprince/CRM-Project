<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Opportunity;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class OpportunityCrudTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_stage_sets_probability_and_expected_revenue(): void
    {
        $user = $this->userWithRole('Sales Representative');
        $account = Account::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->actingAs($user)->post('/opportunities', [
            'name' => 'Enterprise Deal',
            'account_id' => $account->id,
            'amount' => 10000,
            'close_date' => now()->addMonth()->toDateString(),
            'stage' => 'Proposal/Price Quote',
        ])->assertRedirect();

        $opportunity = Opportunity::query()->firstOrFail();

        $this->assertSame(65, $opportunity->probability);
        $this->assertSame(6500.0, $opportunity->expected_revenue);
        $this->assertFalse($opportunity->is_closed);
    }

    public function test_open_opportunity_cannot_have_past_close_date_on_create(): void
    {
        $user = $this->userWithRole('Sales Representative');
        $account = Account::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->from('/opportunities/create')
            ->post('/opportunities', [
                'name' => 'Late Deal',
                'account_id' => $account->id,
                'amount' => 1000,
                'close_date' => now()->subDay()->toDateString(),
                'stage' => 'Qualification',
            ])
            ->assertRedirect('/opportunities/create')
            ->assertSessionHasErrors(['close_date']);
    }

    public function test_stage_change_writes_history(): void
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

        $this->actingAs($user)->put(route('opportunities.update', $opportunity), [
            'name' => $opportunity->name,
            'account_id' => $account->id,
            'amount' => $opportunity->amount,
            'close_date' => $opportunity->close_date->toDateString(),
            'stage' => 'Negotiation/Review',
        ])->assertRedirect();

        $this->assertDatabaseHas('opportunity_stage_histories', [
            'opportunity_id' => $opportunity->id,
            'from_stage' => 'Qualification',
            'to_stage' => 'Negotiation/Review',
            'changed_by' => $user->id,
        ]);
    }
}
