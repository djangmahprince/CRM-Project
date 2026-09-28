<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Lead;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_search_returns_matching_records(): void
    {
        $user = $this->userWithRole('Sales Representative');

        Lead::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'last_name' => 'Zephyr',
            'company' => 'Zephyr Labs',
        ]);

        Account::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'name' => 'Zephyr Holdings',
        ]);

        $this->actingAs($user)
            ->get('/search?q=Zephyr')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Search/Index')
                ->has('results.leads', 1)
                ->has('results.accounts', 1)
            );
    }

    public function test_suggest_requires_two_characters(): void
    {
        $user = $this->userWithRole('Sales Representative');

        $this->actingAs($user)
            ->getJson('/search/suggest?q=Z')
            ->assertSuccessful()
            ->assertJson(['suggestions' => []]);
    }
}
