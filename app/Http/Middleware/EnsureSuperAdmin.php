<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Log;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // DEBUGGING: Force output to verify middleware is executing
        error_log('=== EnsureSuperAdmin MIDDLEWARE EXECUTING ===');
        
        if (!auth()->check()) {
            Log::info('EnsureSuperAdmin: User not authenticated');
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        $user = auth()->user();
        
        Log::info('EnsureSuperAdmin: Checking user', [
            'user_id' => $user->id,
            'org_id' => $user->organization_id,
        ]);
        
        // Set team context to user's organization before checking role
        app(PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? $user->organization_id : null
        );
        
        $hasRole = $user->hasRole('superadmin');
        
        Log::info('EnsureSuperAdmin: Role check result', [
            'has_role' => $hasRole,
            'team_id_set' => config('organizations.enabled') ? $user->organization_id : null,
        ]);

        if (!$hasRole) {
            Log::warning('EnsureSuperAdmin: Access denied - user does not have superadmin role');
            abort(403, 'Unauthorized. SuperAdmin access required.');
        }
        
        Log::info('EnsureSuperAdmin: Access granted');

        return $next($request);
    }
}
