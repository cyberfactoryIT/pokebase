<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Project;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? $user->organization_id : null
        );
        return $this->hasRole($user, ['admin','manager','team','auditor']);
    }

    public function view(User $user, Project $project): bool
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? $user->organization_id : null
        );
        return $this->hasRole($user, ['admin','manager','team','auditor']) && $this->sameOrg($user, $project);
    }

    public function create(User $user): bool
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? $user->organization_id : null
        );
        return $this->hasRole($user, ['admin','manager']);
    }

    public function update(User $user, Project $project): bool
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? $user->organization_id : null
        );
        return $this->hasRole($user, ['admin','manager']) && $this->sameOrg($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? $user->organization_id : null
        );
        return $this->hasRole($user, ['admin','manager']) && $this->sameOrg($user, $project);
    }

    public function restore(User $user, Project $project): bool
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? $user->organization_id : null
        );
        return $this->hasRole($user, ['admin','manager']) && $this->sameOrg($user, $project);
    }

    public function forceDelete(User $user, Project $project): bool
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(
            config('organizations.enabled') ? $user->organization_id : null
        );
        return $this->hasRole($user, ['admin','manager']) && $this->sameOrg($user, $project);
    }
    
    private function hasRole(User $user, array $roles): bool
    {
        if (method_exists($user, 'hasAnyRole')) {
            $result = $user->hasAnyRole($roles);
            \Log::info('ProjectPolicy::hasAnyRole', [
                'user_id' => $user->id,
                'roles' => $roles,
                'result' => $result,
                'team_id' => config('organizations.enabled') ? $user->organization_id : null,
            ]);
            return $result;
        }
        $result = in_array($user->role, $roles, true);
        \Log::info('ProjectPolicy::role_string', [
            'user_id' => $user->id,
            'roles' => $roles,
            'user_role' => $user->role ?? null,
            'result' => $result,
        ]);
        return $result;
    }

    private function sameOrg(User $user, Project $project): bool
    {
        if (!config('organizations.enabled')) {
            // When organizations are disabled, don't restrict by org
            return true;
        }
        return $user->organization_id === $project->organization_id;
    }
}
