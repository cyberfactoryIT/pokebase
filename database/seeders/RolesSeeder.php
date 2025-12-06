<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin' => [
                'org.manage', 'user.manage', 'project.view', 'project.manage', 'survey.view', 'survey.manage', 'response.view', 'report.generate', 'report.view', 'audit.view',
            ],
            'manager' => [
                'user.manage', 'project.manage', 'project.view', 'survey.manage', 'survey.view', 'response.view', 'report.generate', 'report.view',
            ],
            'team_member' => [
                'project.view', 'survey.view', 'response.view', 'report.view',
            ],
            'guest' => [
                'project.view', 'survey.view', 'report.view',
            ],
            'auditor' => [
                'project.view', 'survey.view', 'response.view', 'report.view', 'audit.view',
            ],
        ];
    // Use team-scoped roles only when orgs enabled; otherwise use global (null)
    if (config('organizations.enabled')) {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(1);
    } else {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(null);
    }
        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
            $permissions = Permission::whereIn('name', $perms)->get();
            $role->syncPermissions($permissions);
        }
    }
}
