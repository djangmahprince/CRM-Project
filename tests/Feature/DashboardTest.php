<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Task;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_dashboard_renders_an_inertia_page(): void
    {
        $user = $this->userWithRole('Sales Representative');

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('title', 'Home')
            ->has('metrics')
            ->has('funnel')
            ->has('keyDeals')
            ->has('recentRecords')
            ->has('todaysTasks')
            ->has('todaysEvents')
            ->has('assistantInsights')
        );
    }

    public function test_dashboard_includes_todays_tasks_and_events(): void
    {
        $user = $this->userWithRole('Sales Representative');

        Task::factory()->create([
            'owner_id' => $user->id,
            'assigned_to_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'subject' => 'Call prospect',
            'due_date' => now()->toDateString(),
            'status' => 'Not Started',
        ]);

        Event::factory()->create([
            'owner_id' => $user->id,
            'assigned_to_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'subject' => 'Kickoff meeting',
            'starts_at' => now()->setTime(9, 0),
            'ends_at' => now()->setTime(10, 0),
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->has('todaysTasks', 1)
                ->where('todaysTasks.0.subject', 'Call prospect')
                ->has('todaysEvents', 1)
                ->where('todaysEvents.0.subject', 'Kickoff meeting')
            );
    }
}
