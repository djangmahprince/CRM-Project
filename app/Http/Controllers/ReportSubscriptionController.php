<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportSubscriptionRequest;
use App\Models\Report;
use App\Models\ReportSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportSubscriptionController extends Controller
{
    public function store(StoreReportSubscriptionRequest $request, Report $report): RedirectResponse
    {
        abort_unless(
            (int) $report->owner_id === (int) $request->user()->id
            || $request->user()->can('records.view-all')
            || $report->is_system,
            403
        );

        ReportSubscription::query()->updateOrCreate(
            [
                'report_id' => $report->id,
                'user_id' => $request->user()->id,
            ],
            [
                'frequency' => $request->string('frequency')->toString(),
                'send_day' => $request->input('send_day'),
                'send_time' => $request->string('send_time')->toString() ?: '08:00:00',
            ]
        );

        return back()->with('success', 'Report subscription saved.');
    }

    public function destroy(Request $request, ReportSubscription $subscription): RedirectResponse
    {
        abort_unless((int) $subscription->user_id === (int) $request->user()->id, 403);
        $subscription->delete();

        return back()->with('success', 'Subscription removed.');
    }
}
