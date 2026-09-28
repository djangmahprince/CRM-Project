<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionIsActive
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $idleMinutes = (int) config('crm.session_idle_minutes', 120);
        $lastActivity = $request->session()->get('last_activity_at');

        if ($lastActivity && now()->timestamp - (int) $lastActivity > ($idleMinutes * 60)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => __('Your session expired due to inactivity.')]);
        }

        $request->session()->put('last_activity_at', now()->timestamp);

        return $next($request);
    }
}
