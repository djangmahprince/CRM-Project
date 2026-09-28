<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Models\Note;
use App\Support\CrmRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class NoteController extends Controller
{
    public function store(StoreNoteRequest $request): RedirectResponse
    {
        $type = $request->string('notable_type')->toString();
        $class = CrmRegistry::modelFor($type);
        abort_unless($class, 422);

        $record = $class::query()->findOrFail($request->integer('notable_id'));
        Gate::authorize('view', $record);

        Note::query()->create([
            'title' => $request->string('title')->toString() ?: null,
            'body' => $request->string('body')->toString(),
            'notable_type' => $type,
            'notable_id' => $record->getKey(),
            'owner_id' => $request->user()->id,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Note added.');
    }

    public function destroy(Note $note): RedirectResponse
    {
        abort_unless((int) $note->owner_id === (int) auth()->id() || auth()->user()?->can('records.manage-all'), 403);
        $note->delete();

        return back()->with('success', 'Note deleted.');
    }
}
