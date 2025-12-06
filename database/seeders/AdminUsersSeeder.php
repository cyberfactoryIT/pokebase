<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\Organization;
use App\Models\User;

use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;


class AdminUsersSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function() {
            
            $org = Organization::firstOrCreate(
            ['id' => 1], // forza id=1
            [
                'name' => 'Default Organization',
                'code' => 'ORG1',
                'slug' => Str::slug('Default Organization'),
                'timezone' => 'Europe/Rome',
            ]
        );

            $superadmin = User::updateOrCreate(
                ['email' => 'superadmin@example.com'],
                [
                    'name' => 'Super Admin',
                    'organization_id' => 1,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $saRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId(1);
            $superadmin->assignRole($saRole);
                
           
        });
    }


}
