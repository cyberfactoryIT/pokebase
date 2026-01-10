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
            $teamId = config('organizations.enabled') ? Auth::user()->organization_id : null;
            app(PermissionRegistrar::class)->setPermissionsTeamId($teamId);
            
            // Store in request attributes for persistence
            $request->attributes->set('spatie_team_id', $teamId);
        }
        return $next($request);
    }
}
