<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AssistantDismissal;
use App\Models\Opportunity;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class HomeAssistantTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_dashboard_surfaces_and_dismisses_rule_based_insights(): void
    {
        $user = $this->userWithRole('Sales Representative');

        $account = Account::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'name' => 'Quiet Corp',
        ]);
        $account->forceFill(['updated_at' => now()->subDays(45)])->saveQuietly();

        $opportunity = Opportunity::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'account_id' => $account->id,
            'name' => 'Stale Deal',
            'stage' => 'Negotiation/Review',
            'close_date' => now()->addDays(3)->toDateString(),
            'is_closed' => false,
        ]);
        $opportunity->forceFill(['updated_at' => now()->subDays(14)])->saveQuietly();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->has('assistantInsights', 2)
            );

        $this->actingAs($user)->post('/assistant/dismissals', [
            'key' => 'account-stale-'.$account->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('assistant_dismissals', [
            'user_id' => $user->id,
            'key' => 'account-stale-'.$account->id,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->has('assistantInsights', 1)
                ->where('assistantInsights.0.key', 'opportunity-stale-close-'.$opportunity->id)
            );

        $this->assertSame(1, AssistantDismissal::query()->count());
    }
}
