<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GdprController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeAdmin($request);

        return Inertia::render('Admin/Gdpr', [
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function export(Request $request, User $user): StreamedResponse
    {
        $this->authorizeAdmin($request);

        $payload = [
            'user' => $user->only(['id', 'name', 'email', 'created_at']),
            'leads' => Lead::query()->where('owner_id', $user->id)->get(),
            'accounts' => Account::query()->where('owner_id', $user->id)->get(),
            'contacts' => Contact::query()->where('owner_id', $user->id)->get(),
            'opportunities' => Opportunity::query()->where('owner_id', $user->id)->get(),
            'cases' => CrmCase::query()->where('owner_id', $user->id)->get(),
        ];

        $filename = 'gdpr-export-user-'.$user->id.'.json';

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);
        abort_if((int) $user->id === (int) $request->user()->id, 422, 'Cannot delete your own admin account via GDPR flow.');

        DB::transaction(function () use ($user): void {
            $user->tokens()->delete();
            $user->forceFill([
                'name' => 'Deleted User '.$user->id,
                'email' => 'deleted+'.$user->id.'@example.invalid',
                'password' => bcrypt(str()->random(40)),
                'mfa_enabled' => false,
                'mfa_secret' => null,
            ])->save();
            $user->syncRoles([]);
        });

        return back()->with('success', 'User data anonymized.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('System Administrator'), 403);
    }
}
