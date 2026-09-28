<?php

namespace App\Http\Controllers;

use App\Models\CrmCase;
use App\Models\Event;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $today = now()->toDateString();

        $openLeads = Lead::query()->visibleTo($user)->where('converted', false)->count();
        $openCases = CrmCase::query()->visibleTo($user)->where('is_closed', false)->count();

        $openOpps = Opportunity::query()
            ->visibleTo($user)
            ->where('is_closed', false)
            ->get(['amount', 'probability', 'stage', 'lead_source']);

        $pipelineValue = $openOpps->sum(fn (Opportunity $opp) => ((float) $opp->amount) * ((int) $opp->probability) / 100);

        $funnel = collect(config('crm.opportunity_stages'))
            ->keys()
            ->map(fn (string $stage) => [
                'stage' => $stage,
                'count' => $openOpps->where('stage', $stage)->count(),
                'amount' => round($openOpps->where('stage', $stage)->sum(fn ($o) => (float) $o->amount), 2),
            ])
            ->values();

        $revenueBySource = Opportunity::query()
            ->visibleTo($user)
            ->where('is_closed', false)
            ->select('lead_source', DB::raw('SUM(amount * probability / 100) as expected_revenue'))
            ->groupBy('lead_source')
            ->orderByDesc('expected_revenue')
            ->get()
            ->map(fn ($row) => [
                'source' => $row->lead_source ?: 'Unknown',
                'expected_revenue' => round((float) $row->expected_revenue, 2),
            ]);

        $todaysTasks = Task::query()
            ->visibleTo($user)
            ->whereDate('due_date', $today)
            ->where('status', '!=', 'Completed')
            ->orderBy('due_date')
            ->limit(10)
            ->get(['id', 'subject', 'status', 'priority', 'due_date']);

        $todaysEvents = Event::query()
            ->visibleTo($user)
            ->whereDate('starts_at', $today)
            ->orderBy('starts_at')
            ->limit(10)
            ->get(['id', 'subject', 'starts_at', 'ends_at', 'location']);

        return Inertia::render('Dashboard', [
            'title' => 'Good morning',
            'metrics' => [
                'open_leads' => $openLeads,
                'pipeline_value' => round($pipelineValue, 2),
                'open_cases' => $openCases,
            ],
            'funnel' => $funnel,
            'revenueBySource' => $revenueBySource,
            'todaysTasks' => $todaysTasks,
            'todaysEvents' => $todaysEvents,
        ]);
    }
}
