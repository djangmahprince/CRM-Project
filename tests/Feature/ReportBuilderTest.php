<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Report;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class ReportBuilderTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_user_can_save_and_run_a_custom_report(): void
    {
        $user = $this->userWithRole('Sales Representative');

        Lead::factory()->create([
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'company' => 'Report Co',
            'last_name' => 'Lee',
        ]);

        $this->actingAs($user)->post('/reports/builder', [
            'name' => 'My Leads',
            'report_type' => 'leads',
            'definition' => [
                'columns' => ['last_name', 'company'],
                'filters' => [],
                'chart' => 'none',
            ],
        ])->assertRedirect();

        $report = Report::query()->firstOrFail();

        $this->actingAs($user)
            ->get(route('report-builder.show', $report))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Reports/CustomShow')
                ->has('rows', 1)
            );
    }
}
