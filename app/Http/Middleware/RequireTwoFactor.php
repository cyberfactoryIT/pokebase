<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RequireTwoFactor
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
    // \Log::debug('RequireTwoFactor: session 2fa_passed = ' . var_export(session('2fa_passed'), true) . ' | user_id = ' . ($user ? $user->id : 'null') . ' | route = ' . $request->route()->getName() . ' | method = ' . $request->method());
        if ($user && $user->two_factor_enabled && !session('2fa_passed')) {
            $route = $request->route()->getName();
            if ($route !== '2fa.challenge.show' && $route !== '2fa.challenge.do' && $route !== 'logout') {
                // \Log::debug('RequireTwoFactor: INTERCEPTED, redirect to challenge.show');
                return redirect()->route('2fa.challenge.show');
            }
        }
        return $next($request);
    }
}
