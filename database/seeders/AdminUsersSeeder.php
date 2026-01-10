<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

class AdminUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Crea organizzazione di default (id=1)
        $org = Organization::firstOrCreate(
            ['id' => 1],
            [
                'name'     => 'Default Organization',
                'code'     => 'ORG1',
                'slug'     => Str::slug('Default Organization'),
                'timezone' => 'Europe/Rome',
            ]
        );

        // Crea o aggiorna l'utente superadmin
        $superadmin = User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name'              => 'Super Admin',
                'organization_id'   => $org ? $org->id : 1,
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Crea il ruolo superadmin come GLOBALE (team_id = null)
        $registrar = app(PermissionRegistrar::class);
        
        // Set to NULL to create a global role
        $registrar->setPermissionsTeamId(null);
        
        $saRole = Role::firstOrCreate([
            'name'       => 'superadmin',
            'guard_name' => 'web',
        ]);

        // Assegna il ruolo superadmin con il team dell'utente (required by DB)
        // ma il ruolo stesso è globale, quindi funzionerà su tutte le org
        $registrar->setPermissionsTeamId($org ? $org->id : 1);
        $superadmin->assignRole($saRole);
    }
}
