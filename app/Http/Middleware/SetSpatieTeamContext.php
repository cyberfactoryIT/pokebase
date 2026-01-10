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
            // Always use organization_id for team context (required for team-based roles)
            $teamId = Auth::user()->organization_id;
            app(PermissionRegistrar::class)->setPermissionsTeamId($teamId);
            
            // Store in request attributes for persistence
            $request->attributes->set('spatie_team_id', $teamId);
        }
        return $next($request);
    }
}
