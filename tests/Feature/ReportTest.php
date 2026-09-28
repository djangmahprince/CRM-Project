<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CrmCase;
use App\Models\Opportunity;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_pipeline_report_renders_and_exports_csv(): void
    {
        $user = $this->userWithRole('Sales Representative');
        $account = Account::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Opportunity::factory()->create([
            'account_id' => $account->id,
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'name' => 'Year Deal',
            'close_date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get('/reports/pipeline-this-year')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Reports/Show')
                ->has('rows', 1)
            );

        $this->actingAs($user)
            ->get('/reports/pipeline-this-year/export')
            ->assertSuccessful()
            ->assertHeader('content-disposition');
    }

    public function test_open_cases_report_lists_open_cases_only(): void
    {
        $user = $this->userWithRole('Service Representative');

        CrmCase::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'status' => 'New',
        ]);

        CrmCase::factory()->closed()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get('/reports/open-cases')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->has('rows', 1));
    }
}
