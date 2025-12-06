<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class SetSpatieTeamContext
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            app(PermissionRegistrar::class)->setPermissionsTeamId(
                config('organizations.enabled') ? Auth::user()->organization_id : null
            );
        }
        return $next($request);
    }
}
