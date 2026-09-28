<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportDefinitionRequest;
use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportBuilderController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Reports/Builder', [
            'reportTypes' => [
                'leads' => ['last_name', 'company', 'email', 'lead_status', 'lead_source', 'created_at'],
                'accounts' => ['name', 'type', 'industry', 'phone', 'created_at'],
                'contacts' => ['last_name', 'first_name', 'email', 'title', 'created_at'],
                'opportunities' => ['name', 'stage', 'amount', 'close_date', 'lead_source', 'created_at'],
                'cases' => ['case_number', 'subject', 'status', 'priority', 'origin', 'created_at'],
            ],
        ]);
    }

    public function store(StoreReportDefinitionRequest $request): RedirectResponse
    {
        $report = Report::query()->create([
            'name' => $request->string('name')->toString(),
            'description' => $request->string('description')->toString() ?: null,
            'folder' => $request->string('folder')->toString() ?: 'Private Reports',
            'report_type' => $request->string('report_type')->toString(),
            'definition' => $request->input('definition'),
            'is_system' => false,
            'owner_id' => $request->user()->id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('report-builder.show', $report)->with('success', 'Report saved.');
    }

    public function show(Request $request, Report $report): Response
    {
        abort_unless(
            (int) $report->owner_id === (int) $request->user()->id
            || $request->user()->can('records.view-all'),
            403
        );

        return Inertia::render('Reports/CustomShow', [
            'report' => $report,
            'rows' => $this->runReport($request, $report),
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function runReport(Request $request, Report $report): array
    {
        $columns = $report->definition['columns'] ?? [];
        $filters = $report->definition['filters'] ?? [];
        $user = $request->user();

        $query = match ($report->report_type) {
            'accounts' => Account::query()->visibleTo($user),
            'contacts' => Contact::query()->visibleTo($user),
            'opportunities' => Opportunity::query()->visibleTo($user),
            'cases' => CrmCase::query()->visibleTo($user),
            default => Lead::query()->visibleTo($user),
        };

        foreach ($filters as $filter) {
            $field = $filter['field'] ?? null;
            $value = $filter['value'] ?? null;
            if ($field && $value !== null && $value !== '' && in_array($field, $columns, true)) {
                $query->where($field, 'like', '%'.$value.'%');
            }
        }

        return $query->limit(200)->get($columns ?: ['id'])->map(fn ($row) => $row->only($columns))->all();
    }
}
