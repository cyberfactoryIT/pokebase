<?php
namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;


trait EnforcesSuperAdmin
{
    protected function enforceSuperAdmin(): void
    {
        $u = Auth::user();
        app(PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? ($u->organization_id ?? null) : null
        );
        if (!$u || !($u->hasRole('superadmin') ?? false)) {
            abort(403);
        }
    }
}
