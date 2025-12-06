<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'org.manage',
            'user.manage',
            'project.view',
            'project.manage',
            'survey.view',
            'survey.manage',
            'response.view',
            'report.generate',
            'report.view',
            'audit.view',
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
