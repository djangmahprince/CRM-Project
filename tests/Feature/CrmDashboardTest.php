<?php

namespace Tests\Feature;

use App\Models\Dashboard;
use App\Models\Report;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class CrmDashboardTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_user_can_create_and_view_a_dashboard_with_widgets(): void
    {
        $user = $this->userWithRole('Sales Representative');

        $report = Report::query()->create([
            'name' => 'Lead list',
            'report_type' => 'leads',
            'definition' => ['columns' => ['last_name', 'company'], 'filters' => []],
            'owner_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->post('/crm-dashboards', [
            'name' => 'Sales pulse',
            'description' => 'Daily view',
            'auto_refresh_minutes' => 5,
            'widgets' => [
                [
                    'title' => 'Leads',
                    'type' => 'table',
                    'report_id' => $report->id,
                ],
            ],
        ])->assertRedirect();

        $dashboard = Dashboard::query()->firstOrFail();

        $this->actingAs($user)
            ->get(route('crm-dashboards.show', $dashboard))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboards/Show')
                ->where('dashboard.name', 'Sales pulse')
                ->has('widgets', 1)
            );
    }
}
