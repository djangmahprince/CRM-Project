<?php

namespace App\Http\Controllers;

use App\Models\CrmCase;
use App\Models\Event;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\RecentlyViewed;
use App\Models\Task;
use App\Support\CrmRegistry;
use App\Support\HomeAssistant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, HomeAssistant $assistant): Response
    {
        $user = $request->user();
        $today = now()->toDateString();
        $from = $request->filled('from')
            ? $request->date('from')?->toDateString()
            : now()->startOfYear()->toDateString();
        $to = $request->filled('to')
            ? $request->date('to')?->toDateString()
            : now()->endOfYear()->toDateString();

        $openLeads = Lead::query()->visibleTo($user)->where('converted', false)->count();
        $openCases = CrmCase::query()->visibleTo($user)->where('is_closed', false)->count();

        $openOpps = Opportunity::query()
            ->visibleTo($user)
            ->where('is_closed', false)
            ->when($from, fn ($q) => $q->whereDate('close_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('close_date', '<=', $to))
            ->get(['id', 'name', 'amount', 'probability', 'stage', 'lead_source', 'close_date', 'account_id']);

        $pipelineValue = $openOpps->sum(fn (Opportunity $opp) => ((float) $opp->amount) * ((int) $opp->probability) / 100);
        $pipelineAmount = $openOpps->sum(fn (Opportunity $opp) => (float) $opp->amount);

        $funnel = collect(config('crm.opportunity_stages'))
            ->keys()
            ->map(fn (string $stage) => [
                'stage' => $stage,
                'count' => $openOpps->where('stage', $stage)->count(),
                'amount' => round($openOpps->where('stage', $stage)->sum(fn ($o) => (float) $o->amount), 2),
                'percent' => $pipelineAmount > 0
                    ? round(($openOpps->where('stage', $stage)->sum(fn ($o) => (float) $o->amount) / $pipelineAmount) * 100, 1)
                    : 0,
            ])
            ->values();

        $revenueBySource = Opportunity::query()
            ->visibleTo($user)
            ->where('is_closed', false)
            ->when($from, fn ($q) => $q->whereDate('close_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('close_date', '<=', $to))
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

        $keyDeals = Opportunity::query()
            ->visibleTo($user)
            ->where('is_closed', false)
            ->when($from, fn ($q) => $q->whereDate('close_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('close_date', '<=', $to))
            ->with(['account:id,name'])
            ->orderByDesc('amount')
            ->limit(5)
            ->get(['id', 'name', 'amount', 'stage', 'probability', 'close_date', 'account_id'])
            ->map(fn (Opportunity $opp) => [
                'id' => $opp->id,
                'name' => $opp->name,
                'amount' => (float) $opp->amount,
                'expected_revenue' => $opp->expected_revenue,
                'stage' => $opp->stage,
                'close_date' => optional($opp->close_date)?->toDateString(),
                'account' => $opp->account?->name,
            ]);

        $recentRecords = RecentlyViewed::query()
            ->where('user_id', $user->id)
            ->latest('viewed_at')
            ->limit(8)
            ->get()
            ->map(function (RecentlyViewed $row) {
                $class = CrmRegistry::modelFor($row->viewable_type);
                if (! $class) {
                    return null;
                }
                $record = $class::query()->find($row->viewable_id);
                if (! $record) {
                    return null;
                }
                $label = match ($row->viewable_type) {
                    'lead' => trim(($record->first_name ?? '').' '.($record->last_name ?? '')),
                    'contact' => trim(($record->first_name ?? '').' '.($record->last_name ?? '')),
                    'case' => $record->case_number.' — '.$record->subject,
                    default => $record->name ?? $record->subject ?? '#'.$record->id,
                };
                $route = match ($row->viewable_type) {
                    'lead' => route('leads.show', $record),
                    'account' => route('accounts.show', $record),
                    'contact' => route('contacts.show', $record),
                    'opportunity' => route('opportunities.show', $record),
                    'case' => route('cases.show', $record),
                    default => null,
                };

                return $route ? [
                    'object' => $row->viewable_type,
                    'label' => $label,
                    'url' => $route,
                    'viewed_at' => optional($row->viewed_at)?->toIso8601String(),
                ] : null;
            })
            ->filter()
            ->values();

        return Inertia::render('Dashboard', [
            'title' => 'Home',
            'metrics' => [
                'open_leads' => $openLeads,
                'pipeline_value' => round($pipelineValue, 2),
                'pipeline_amount' => round($pipelineAmount, 2),
                'open_cases' => $openCases,
                'open_opportunities' => $openOpps->count(),
            ],
            'funnel' => $funnel,
            'revenueBySource' => $revenueBySource,
            'todaysTasks' => $todaysTasks,
            'todaysEvents' => $todaysEvents,
            'keyDeals' => $keyDeals,
            'recentRecords' => $recentRecords,
            'filters' => [
                'from' => $from,
                'to' => $to,
            ],
            'assistantInsights' => $assistant->insightsFor($user),
        ]);
    }
}
