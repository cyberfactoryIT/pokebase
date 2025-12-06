<?php
namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

trait EnforcesAdmin
{
    protected function enforceAdmin(): void
    {
        $u = Auth::user();
        app(PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? ($u->organization_id ?? null) : null
        );
        if (!$u || !($u->hasRole('admin') ?? false)) {
            abort(403);
        }
    }
}
