<?php

namespace Tests\Feature;

use App\Notifications\RecordAssignedNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class NotificationInboxTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_notifications_page_lists_database_notifications(): void
    {
        $user = $this->userWithRole('Sales Representative');

        $user->notifyNow(new RecordAssignedNotification('task', 'Follow up', '/tasks/1'));

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Notifications/Index')
                ->has('notifications', 1)
                ->where('notifications.0.data.subject', 'Follow up')
            );
    }

    public function test_mark_all_read_clears_unread_count(): void
    {
        $user = $this->userWithRole('Sales Representative');
        $user->notifyNow(new RecordAssignedNotification('task', 'Due soon', '/tasks/2'));

        $this->assertSame(1, $user->unreadNotifications()->count());

        $this->actingAs($user)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }
}
