<?php

namespace App\Http\Controllers;

use App\Models\CrmCase;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Report;
use App\Support\ReportExporter;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Reports/Index', [
            'reports' => [
                ['key' => 'pipeline-this-year', 'name' => 'Pipeline this year', 'description' => 'Open and closed opportunities with close dates this year.'],
                ['key' => 'leads-by-source', 'name' => 'Leads by source', 'description' => 'Lead counts grouped by lead source.'],
                ['key' => 'open-cases', 'name' => 'Open cases', 'description' => 'All cases that are not closed.'],
            ],
            'customReports' => Report::query()
                ->where(function ($q) use ($request) {
                    $q->where('owner_id', $request->user()->id);
                    if ($request->user()->can('records.view-all')) {
                        $q->orWhere('is_system', true);
                    }
                })
                ->latest()
                ->get(['id', 'name', 'description', 'report_type']),
            'canBuild' => true,
        ]);
    }

    public function show(Request $request, string $report): Response|HttpResponse
    {
        $data = $this->reportData($request, $report);

        return Inertia::render('Reports/Show', [
            'report' => $report,
            'title' => $data['title'],
            'columns' => $data['columns'],
            'rows' => $data['rows'],
            'recordCount' => count($data['rows']),
        ]);
    }

    public function export(Request $request, string $report, ReportExporter $exporter): StreamedResponse
    {
        $data = $this->reportData($request, $report);
        $format = strtolower($request->string('format')->toString() ?: 'csv');

        return $exporter->download($data, $report, $format);
    }

    /**
     * @return array{title: string, columns: list<string>, rows: list<array<string, mixed>>}
     */
    private function reportData(Request $request, string $report): array
    {
        $user = $request->user();

        return match ($report) {
            'pipeline-this-year' => [
                'title' => 'Pipeline this year',
                'columns' => ['Name', 'Account', 'Stage', 'Amount', 'Close Date', 'Owner'],
                'rows' => Opportunity::query()
                    ->visibleTo($user)
                    ->with(['account:id,name', 'owner:id,name'])
                    ->whereYear('close_date', now()->year)
                    ->orderBy('close_date')
                    ->get()
                    ->map(fn (Opportunity $opp) => [
                        'name' => $opp->name,
                        'account' => $opp->account?->name,
                        'stage' => $opp->stage,
                        'amount' => $opp->amount,
                        'close_date' => optional($opp->close_date)?->toDateString(),
                        'owner' => $opp->owner?->name,
                    ])->all(),
            ],
            'leads-by-source' => [
                'title' => 'Leads by source',
                'columns' => ['Lead Source', 'Count'],
                'rows' => Lead::query()
                    ->visibleTo($user)
                    ->select('lead_source', DB::raw('COUNT(*) as total'))
                    ->groupBy('lead_source')
                    ->orderByDesc('total')
                    ->get()
                    ->map(fn ($row) => [
                        'lead_source' => $row->lead_source ?: 'Unknown',
                        'count' => $row->total,
                    ])->all(),
            ],
            'open-cases' => [
                'title' => 'Open cases',
                'columns' => ['Case Number', 'Subject', 'Status', 'Priority', 'Account', 'Owner'],
                'rows' => CrmCase::query()
                    ->visibleTo($user)
                    ->with(['account:id,name', 'owner:id,name'])
                    ->where('is_closed', false)
                    ->orderByDesc('created_at')
                    ->get()
                    ->map(fn (CrmCase $case) => [
                        'case_number' => $case->case_number,
                        'subject' => $case->subject,
                        'status' => $case->status,
                        'priority' => $case->priority,
                        'account' => $case->account?->name,
                        'owner' => $case->owner?->name,
                    ])->all(),
            ],
            default => abort(404),
        };
    }
}
