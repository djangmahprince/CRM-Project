<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDashboardRequest;
use App\Models\Dashboard;
use App\Models\DashboardWidget;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CrmDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $dashboards = Dashboard::query()
            ->where('owner_id', $request->user()->id)
            ->withCount('widgets')
            ->latest()
            ->get(['id', 'name', 'description', 'folder', 'auto_refresh_minutes', 'created_at']);

        return Inertia::render('Dashboards/Index', [
            'dashboards' => $dashboards,
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Dashboards/Create', [
            'reports' => Report::query()
                ->where(function ($q) use ($request): void {
                    $q->where('owner_id', $request->user()->id)
                        ->orWhere('is_system', true);
                })
                ->orderBy('name')
                ->get(['id', 'name', 'report_type']),
        ]);
    }

    public function store(StoreDashboardRequest $request): RedirectResponse
    {
        $dashboard = Dashboard::query()->create([
            'name' => $request->string('name')->toString(),
            'description' => $request->input('description'),
            'folder' => $request->string('folder')->toString() ?: 'Private Dashboards',
            'auto_refresh_minutes' => $request->integer('auto_refresh_minutes') ?: null,
            'owner_id' => $request->user()->id,
            'created_by' => $request->user()->id,
        ]);

        foreach ($request->input('widgets', []) as $index => $widget) {
            DashboardWidget::query()->create([
                'dashboard_id' => $dashboard->id,
                'report_id' => $widget['report_id'] ?? null,
                'title' => $widget['title'],
                'type' => $widget['type'] ?? 'table',
                'x' => $widget['x'] ?? ($index % 2) * 6,
                'y' => $widget['y'] ?? intdiv($index, 2) * 4,
                'w' => $widget['w'] ?? 6,
                'h' => $widget['h'] ?? 4,
                'options' => $widget['options'] ?? [],
            ]);
        }

        return redirect()->route('crm-dashboards.show', $dashboard)->with('success', 'Dashboard created.');
    }

    public function show(Request $request, Dashboard $dashboard): Response
    {
        abort_unless((int) $dashboard->owner_id === (int) $request->user()->id, 403);

        $dashboard->load(['widgets.report:id,name,report_type,definition']);

        $widgets = $dashboard->widgets->map(function (DashboardWidget $widget) use ($request) {
            $rows = [];
            if ($widget->report) {
                $rows = app(ReportBuilderController::class)->previewRows($request, $widget->report);
            }

            return [
                'id' => $widget->id,
                'title' => $widget->title,
                'type' => $widget->type,
                'x' => $widget->x,
                'y' => $widget->y,
                'w' => $widget->w,
                'h' => $widget->h,
                'report' => $widget->report?->only(['id', 'name', 'report_type']),
                'rows' => $rows,
            ];
        });

        return Inertia::render('Dashboards/Show', [
            'dashboard' => $dashboard->only([
                'id', 'name', 'description', 'folder', 'auto_refresh_minutes',
            ]),
            'widgets' => $widgets,
        ]);
    }

    public function destroy(Request $request, Dashboard $dashboard): RedirectResponse
    {
        abort_unless((int) $dashboard->owner_id === (int) $request->user()->id, 403);
        $dashboard->delete();

        return redirect()->route('crm-dashboards.index')->with('success', 'Dashboard deleted.');
    }
}
