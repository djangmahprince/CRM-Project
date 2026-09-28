<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class EventCalendarTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_calendar_lists_events_in_range(): void
    {
        $user = $this->userWithRole('Sales Representative');

        Event::factory()->create([
            'owner_id' => $user->id,
            'assigned_to_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'subject' => 'Demo call',
            'starts_at' => now()->startOfWeek()->addHours(10),
            'ends_at' => now()->startOfWeek()->addHours(11),
        ]);

        $this->actingAs($user)
            ->get('/calendar?view=week&date='.now()->toDateString())
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Calendar/Index')
                ->has('events', 1)
            );
    }

    public function test_user_can_reschedule_an_event(): void
    {
        $user = $this->userWithRole('Sales Representative');

        $event = Event::factory()->create([
            'owner_id' => $user->id,
            'assigned_to_id' => $user->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'subject' => 'Reschedule me',
            'starts_at' => now()->setTime(10, 0),
            'ends_at' => now()->setTime(11, 0),
        ]);

        $startsAt = now()->addDay()->setTime(14, 0);
        $endsAt = now()->addDay()->setTime(15, 0);

        $this->actingAs($user)
            ->patch(route('events.reschedule', $event), [
                'starts_at' => $startsAt->toIso8601String(),
                'ends_at' => $endsAt->toIso8601String(),
            ])
            ->assertRedirect();

        $event->refresh();

        $this->assertTrue($event->starts_at->equalTo(Carbon::parse($startsAt)));
        $this->assertTrue($event->ends_at->equalTo(Carbon::parse($endsAt)));
    }
}
