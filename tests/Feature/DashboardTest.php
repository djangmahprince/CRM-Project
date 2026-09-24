<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dashboard_renders_an_inertia_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('title', 'Good morning')
        );
    }
}
