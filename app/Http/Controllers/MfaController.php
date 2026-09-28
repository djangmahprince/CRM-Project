<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMfaConfirmRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Inertia\Response;
use PragmaRX\Google2FA\Google2FA;

class MfaController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $secret = null;
        $otpauth = null;

        if (! $user->mfa_enabled) {
            $google2fa = new Google2FA;
            $secret = $google2fa->generateSecretKey();
            $request->session()->put('mfa_setup_secret', $secret);
            $otpauth = $google2fa->getQRCodeUrl(config('app.name', 'CRM'), $user->email, $secret);
        }

        return Inertia::render('Settings/Mfa', [
            'enabled' => (bool) $user->mfa_enabled,
            'setupSecret' => $secret,
            'otpauthUrl' => $otpauth,
        ]);
    }

    public function confirm(StoreMfaConfirmRequest $request): RedirectResponse
    {
        $secret = $request->session()->get('mfa_setup_secret');
        abort_unless(is_string($secret) && $secret !== '', 422);

        $google2fa = new Google2FA;
        if (! $google2fa->verifyKey($secret, $request->string('code')->toString())) {
            return back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        $request->user()->forceFill([
            'mfa_enabled' => true,
            'mfa_secret' => Crypt::encryptString($secret),
        ])->save();
        $request->session()->forget('mfa_setup_secret');

        return redirect()->route('mfa.edit')->with('success', 'Multi-factor authentication enabled.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        abort_unless($user->mfa_enabled && $user->mfa_secret, 422);

        $google2fa = new Google2FA;
        $secret = Crypt::decryptString($user->mfa_secret);
        if (! $google2fa->verifyKey($secret, $request->string('code')->toString())) {
            return back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        $user->forceFill([
            'mfa_enabled' => false,
            'mfa_secret' => null,
        ])->save();

        return redirect()->route('mfa.edit')->with('success', 'Multi-factor authentication disabled.');
    }

    public function challenge(): Response
    {
        return Inertia::render('Auth/MfaChallenge');
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $userId = $request->session()->get('mfa_pending_user_id');
        abort_unless($userId, 403);

        $user = User::query()->findOrFail($userId);
        abort_unless($user->mfa_enabled && $user->mfa_secret, 403);

        $google2fa = new Google2FA;
        $secret = Crypt::decryptString($user->mfa_secret);
        if (! $google2fa->verifyKey($secret, $request->string('code')->toString())) {
            return back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        auth()->login($user, (bool) $request->session()->pull('mfa_remember', false));
        $request->session()->forget('mfa_pending_user_id');
        $request->session()->regenerate();
        $request->session()->put('last_activity_at', now()->timestamp);
        $request->session()->put('mfa_verified_at', now()->timestamp);

        return redirect()->intended(route('dashboard'));
    }
}
