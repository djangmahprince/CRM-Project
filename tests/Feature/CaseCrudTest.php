<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CrmCase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class CaseCrudTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_case_number_is_generated_on_create(): void
    {
        $user = $this->userWithRole('Service Representative');
        $account = Account::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->actingAs($user)->post('/cases', [
            'account_id' => $account->id,
            'subject' => 'Printer offline',
            'status' => 'New',
            'origin' => 'Phone',
        ])->assertRedirect();

        $case = CrmCase::query()->firstOrFail();
        $this->assertStringStartsWith('CAS-', $case->case_number);
        $this->assertFalse($case->is_closed);
    }

    public function test_closed_case_is_immutable_until_reopened(): void
    {
        $user = $this->userWithRole('Service Representative');
        $case = CrmCase::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'status' => 'Working',
        ]);

        $this->actingAs($user)->post(route('cases.close', $case))->assertRedirect();
        $case->refresh();
        $this->assertTrue($case->is_closed);

        $this->actingAs($user)
            ->put(route('cases.update', $case), [
                'subject' => 'Changed',
                'status' => 'Working',
                'origin' => $case->origin,
            ])
            ->assertForbidden();

        $this->actingAs($user)->post(route('cases.reopen', $case))->assertRedirect();
        $case->refresh();
        $this->assertFalse($case->is_closed);
        $this->assertSame('Working', $case->status);
    }
}
