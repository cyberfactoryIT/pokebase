<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\ActivityLog;


class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $user = Auth::user();
    ActivityLog::logActivity('login', 'user_login', [], config('organizations.enabled') ? $user->organization_id : null, $user->id);

        // Set locale from user profile if available
        if ($user && $user->locale) {
            session(['locale' => $user->locale]);
            app()->setLocale($user->locale);
        }

        // Remember me custom: crea token/cookie solo con il servizio custom
        $cookie = null;
        if ($request->boolean('remember')) {
            $result = app(\App\Services\RememberTokenService::class)->issue($user, $request);
            // Rimuovi il cookie nativo Laravel
            \Cookie::queue(\Cookie::forget('remember_web_' . $user->id));
            $cookie = cookie('remember_me', $result['cookie_value'], 60*24*30, null, null, true, true, false, 'lax');
            //$cookie = \Cookie::make('remember_me', $result['cookie_value'], $result['expires']->diffInMinutes(\Carbon\Carbon::now()), null, null, true, true, false, 'lax');
        }
        $redirect = redirect()->intended(route('dashboard', absolute: false));
        if ($cookie) {
            \Log::info('DEBUG-login-cookie', [
                'cookie_value' => $result['cookie_value'],
                'expires' => $result['expires']->toDateTimeString(),
            ]);
            return $redirect->withCookie($cookie);
        } else {
            \Log::info('DEBUG-login-cookie', ['cookie' => null]);
            return $redirect;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
    ActivityLog::logActivity('login', 'user_logout', [], config('organizations.enabled') ? Auth::user()->organization_id : null, Auth::id());

    // Revoke remember token and clear cookie custom
    app(\App\Services\RememberTokenService::class)->revokeCurrent($request);
    // Rimuovi il cookie nativo Laravel
    \Cookie::queue(\Cookie::forget('remember_web_' . Auth::id()));

    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
    }
}
