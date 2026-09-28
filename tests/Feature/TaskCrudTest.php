<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Notifications\RecordAssignedNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class TaskCrudTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_user_can_create_a_task_and_assignee_is_notified(): void
    {
        Notification::fake();

        $owner = $this->userWithRole('Sales Representative', ['email' => 'owner@example.com']);
        $assignee = $this->userWithRole('Sales Representative', ['email' => 'assignee@example.com']);

        $this->actingAs($owner)->post('/tasks', [
            'subject' => 'Call prospect',
            'assigned_to_id' => $assignee->id,
            'status' => 'Not Started',
            'priority' => 'Normal',
            'due_date' => now()->addDay()->toDateString(),
        ])->assertRedirect();

        $task = Task::query()->firstOrFail();
        $this->assertSame('Call prospect', $task->subject);

        Notification::assertSentTo($assignee, RecordAssignedNotification::class);
    }
}
