<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class AccountCrudTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_sales_representative_can_create_an_account(): void
    {
        $user = $this->userWithRole('Sales Representative');

        $response = $this->actingAs($user)->post('/accounts', [
            'name' => 'Acme Corp',
            'type' => 'Customer',
        ]);

        $account = Account::query()->firstOrFail();
        $response->assertRedirect(route('accounts.show', $account));
        $this->assertDatabaseHas('accounts', [
            'name' => 'Acme Corp',
            'owner_id' => $user->id,
        ]);
    }

    public function test_account_list_is_owner_scoped_for_sales_reps(): void
    {
        $owner = $this->userWithRole('Sales Representative', ['email' => 'owner@example.com']);
        $other = $this->userWithRole('Sales Representative', ['email' => 'other@example.com']);

        $visible = Account::factory()->create([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
            'name' => 'Visible Co',
        ]);

        Account::factory()->create([
            'owner_id' => $other->id,
            'created_by' => $other->id,
            'updated_by' => $other->id,
            'name' => 'Hidden Co',
        ]);

        $this->actingAs($owner)
            ->get('/accounts')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Accounts/Index')
                ->has('accounts.data', 1)
                ->where('accounts.data.0.id', $visible->id)
            );
    }
}
