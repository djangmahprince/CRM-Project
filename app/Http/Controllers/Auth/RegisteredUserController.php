<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\User;
use App\Support\PasswordHistoryService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    public function create(): RedirectResponse
    {
        return redirect()->route('login', ['tab' => 'register']);
    }

    public function store(RegisterUserRequest $request, PasswordHistoryService $passwordHistory): RedirectResponse
    {
        $user = DB::transaction(function () use ($request, $passwordHistory): User {
            $user = User::query()->create([
                'name' => $request->string('name')->toString(),
                'email' => $request->string('email')->toString(),
                'password' => $request->string('password')->toString(),
            ]);

            $user->assignRole('Sales Representative');
            $passwordHistory->rememberCurrent($user);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('last_activity_at', now()->timestamp);

        return redirect()->intended(route('dashboard'));
    }
}
