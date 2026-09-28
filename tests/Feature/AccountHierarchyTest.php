<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class AccountHierarchyTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_account_show_includes_hierarchy_rollups(): void
    {
        $user = $this->userWithRole('Sales Representative');

        $parent = Account::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'name' => 'Parent Co',
            'employees' => 10,
            'annual_revenue' => 1000,
        ]);

        Account::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'parent_account_id' => $parent->id,
            'name' => 'Child Co',
            'employees' => 5,
            'annual_revenue' => 500,
        ]);

        $this->actingAs($user)
            ->get(route('accounts.show', $parent))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Accounts/Show')
                ->where('rollups.account_count', 2)
                ->where('rollups.total_employees', 15)
            );

        $this->actingAs($user)
            ->get(route('accounts.hierarchy'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Accounts/Hierarchy')
                ->has('tree', 1)
            );
    }
}
