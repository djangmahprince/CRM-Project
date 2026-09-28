<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSavedSearchRequest;
use App\Models\SavedSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SavedSearchController extends Controller
{
    public function store(StoreSavedSearchRequest $request): RedirectResponse
    {
        SavedSearch::query()->create([
            'user_id' => $request->user()->id,
            'name' => $request->string('name')->toString(),
            'object_type' => $request->string('object_type')->toString(),
            'criteria' => [
                'logic' => $request->string('logic')->toString() === 'or' ? 'or' : 'and',
                'conditions' => $request->input('conditions', []),
            ],
        ]);

        return back()->with('success', 'Search saved.');
    }

    public function destroy(Request $request, SavedSearch $savedSearch): RedirectResponse
    {
        abort_unless((int) $savedSearch->user_id === (int) $request->user()->id, 403);
        $savedSearch->delete();

        return back()->with('success', 'Saved search deleted.');
    }
}
