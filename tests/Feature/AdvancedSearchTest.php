<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\SavedSearch;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class AdvancedSearchTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_advanced_search_supports_and_logic_and_saved_searches(): void
    {
        $user = $this->userWithRole('Sales Representative');

        Lead::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'company' => 'Acme',
            'lead_status' => 'New',
            'last_name' => 'Alpha',
        ]);

        Lead::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'company' => 'Beta LLC',
            'lead_status' => 'Working',
            'last_name' => 'Beta',
        ]);

        $this->actingAs($user)
            ->get('/search/advanced?'.http_build_query([
                'object' => 'leads',
                'logic' => 'and',
                'conditions' => [
                    ['field' => 'company', 'operator' => 'contains', 'value' => 'Acme'],
                    ['field' => 'lead_status', 'operator' => 'equals', 'value' => 'New'],
                ],
            ]))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Search/Advanced')
                ->has('results', 1)
                ->where('results.0.label', fn ($label) => str_contains($label, 'Alpha'))
            );

        $this->actingAs($user)->post('/saved-searches', [
            'name' => 'Acme new leads',
            'object_type' => 'leads',
            'logic' => 'and',
            'conditions' => [
                ['field' => 'company', 'operator' => 'contains', 'value' => 'Acme'],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('saved_searches', [
            'user_id' => $user->id,
            'name' => 'Acme new leads',
        ]);

        $saved = SavedSearch::query()->firstOrFail();
        $this->actingAs($user)->delete(route('saved-searches.destroy', $saved))->assertRedirect();
        $this->assertDatabaseMissing('saved_searches', ['id' => $saved->id]);
    }
}
