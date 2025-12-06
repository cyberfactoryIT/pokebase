<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Services\RememberTokenService;

class RememberMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            $service = app(RememberTokenService::class);
            $user = $service->validateAndReauth($request);
            if ($user) {
                Auth::login($user);
            }
        }
        return $next($request);
    }
}
