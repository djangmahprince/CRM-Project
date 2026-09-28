<?php

namespace App\Http\Controllers;

use App\Support\CrmRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BulkActionController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'object' => ['required', Rule::in(['leads', 'accounts', 'contacts', 'opportunities', 'cases', 'tasks'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'action' => ['required', Rule::in(['delete', 'owner', 'status'])],
            'owner_id' => ['required_if:action,owner', 'nullable', 'integer', 'exists:users,id'],
            'status' => ['required_if:action,status', 'nullable', 'string'],
        ]);

        $morphKey = match ($data['object']) {
            'leads' => 'lead',
            'accounts' => 'account',
            'contacts' => 'contact',
            'opportunities' => 'opportunity',
            'cases' => 'case',
            'tasks' => 'task',
        };

        $class = CrmRegistry::modelFor($morphKey);
        abort_unless($class, 422);

        $records = $class::query()
            ->visibleTo($request->user())
            ->whereIn('id', $data['ids'])
            ->get();

        foreach ($records as $record) {
            if ($data['action'] === 'delete') {
                Gate::authorize('delete', $record);
                $record->delete();

                continue;
            }

            Gate::authorize('update', $record);

            if ($data['action'] === 'owner') {
                $record->update(['owner_id' => $data['owner_id']]);
            }

            if ($data['action'] === 'status') {
                $statusField = match ($morphKey) {
                    'lead' => 'lead_status',
                    'case', 'task' => 'status',
                    'opportunity' => 'stage',
                    default => null,
                };
                abort_unless($statusField, 422, 'Status bulk action is not supported for this object.');
                $record->update([$statusField => $data['status']]);
            }
        }

        return back()->with('success', 'Bulk action completed for '.$records->count().' records.');
    }
}
