<?php

namespace Tests\Feature;

use App\Jobs\SendReportSubscriptions;
use App\Models\Report;
use App\Models\ReportSubscription;
use App\Notifications\ReportSubscriptionMail;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\SeedsCrmRoles;
use Tests\TestCase;

class ReportSubscriptionTest extends TestCase
{
    use LazilyRefreshDatabase;
    use SeedsCrmRoles;

    public function test_user_can_subscribe_and_job_sends_mail(): void
    {
        Notification::fake();
        $user = $this->userWithRole('Sales Representative');

        $report = Report::query()->create([
            'name' => 'Weekly pipeline',
            'report_type' => 'opportunities',
            'definition' => ['columns' => ['name'], 'filters' => []],
            'owner_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->post(route('report-subscriptions.store', $report), [
            'frequency' => 'daily',
            'send_time' => '00:00',
        ])->assertRedirect();

        $this->assertDatabaseHas('report_subscriptions', [
            'report_id' => $report->id,
            'user_id' => $user->id,
            'frequency' => 'daily',
        ]);

        (new SendReportSubscriptions)->handle();

        Notification::assertSentTo($user, ReportSubscriptionMail::class);
        $this->assertNotNull(ReportSubscription::query()->first()->last_sent_at);
    }
}
