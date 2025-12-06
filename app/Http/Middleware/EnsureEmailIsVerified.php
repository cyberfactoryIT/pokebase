<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureEmailIsVerified
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $route = $request->route() ? $request->route()->getName() : null;
        $ignoreRoutes = [
            'logout',
            'verification.custom',
            'verification.verify',
            'verification.send',
            'login',
            'register',
        ];
        if ($user && !$user->email_verified_at && !in_array($route, $ignoreRoutes)) {
            return response()->view('auth.verify-required');
        }
        return $next($request);
    }
}
