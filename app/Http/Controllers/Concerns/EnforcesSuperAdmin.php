<?php
namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;


trait EnforcesSuperAdmin
{
    protected function enforceSuperAdmin(): void
    {
        $u = Auth::user();
        
        if (!$u) {
            abort(403);
        }
        
        // Set team context to user's organization to check the role
        app(PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? $u->organization_id : null
        );
        
        if (!$u->hasRole('superadmin')) {
            abort(403);
        }
    }
}
