<?php

namespace App\Http\Controllers;

use App\Models\AssistantDismissal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssistantDismissalController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:120'],
        ]);

        AssistantDismissal::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'key' => $data['key'],
        ]);

        return back()->with('success', 'Insight dismissed.');
    }
}
