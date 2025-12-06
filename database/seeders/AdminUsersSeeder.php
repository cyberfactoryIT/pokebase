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

        // Crea il ruolo superadmin se non esiste
        $saRole = Role::firstOrCreate([
            'name'       => 'superadmin',
            'guard_name' => 'web',
        ]);

        // Imposta il team per Spatie Permission (multi-tenant)
        $registrar = app(PermissionRegistrar::class);

        if (config('organizations.enabled') && $org) {
            $registrar->setPermissionsTeamId($org->id);
        } else {
            $registrar->setPermissionsTeamId(1);
        }

        // Assegna il ruolo al superadmin
        $superadmin->assignRole($saRole);
    }
}
