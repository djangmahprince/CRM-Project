<?php

namespace Tests\Feature;

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
            ->where('title', 'Good morning')
            ->has('metrics')
            ->has('funnel')
        );
    }
}
