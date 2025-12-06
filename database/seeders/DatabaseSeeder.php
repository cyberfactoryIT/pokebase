<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RolesSeeder::class,
            RolesTableSeeder::class, 
            PlansSeeder::class,
            AdminUsersSeeder::class,
            FaqSeeder::class,
            CountrySeeder::class,
            HelpSeeder::class,
            PlansSeeder::class,
            PlansAndFeaturesSeeder::class,
            PromotionsSeeder::class,
        ]);

        // 1) Crea l’organizzazione base
        $org = Organization::firstOrCreate(
            ['id' => 1], // forza id=1
            [
                'name' => 'Default Organization',
                'code' => 'ORG1',
                'slug' => Str::slug('Default Organization'),
                'timezone' => 'Europe/Rome',
            ]
        );

        // 2) Crea o aggiorna l’utente iniziale
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'organization_id' => $org->id,
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );

        // Se l’utente già esiste, assicuriamoci che abbia l’org
        if (!$user->organization_id) {
            $user->organization_id = $org->id;
            $user->save();
        }

        // 3) Assegna ruolo admin nel team corretto
        app(PermissionRegistrar::class)->setPermissionsTeamId($org->id);
            $user->assignRole('admin');
        }
}
