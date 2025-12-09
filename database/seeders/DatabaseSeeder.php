<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Lancia i singoli seeder
        $this->call([
            PermissionSeeder::class,
            RolesSeeder::class,
            RolesTableSeeder::class,
            PlansSeeder::class,
            AdminUsersSeeder::class,
            FaqSeeder::class,
            CountrySeeder::class,
            HelpSeeder::class,
            PlansAndFeaturesSeeder::class,
            PromotionsSeeder::class,
            GameSeeder::class,
            CardCatalogDemoSeeder::class,
        ]);

        // Crea organizzazione di default (se la feature è abilitata)
        $org = null;

        if (config('organizations.enabled')) {
            $org = Organization::firstOrCreate(
                ['id' => 1],
                [
                    'name'     => 'Default Organization',
                    'code'     => 'ORG1',
                    'slug'     => Str::slug('Default Organization'),
                    'timezone' => 'Europe/Rome',
                ]
            );
        }

    // Crea o recupera l'utente iniziale
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'organization_id' => optional($org)->id,
                'name'            => 'Test User',
                'password'        => Hash::make('password'),
            ]
        );

        // Assegna il ruolo admin
        if (config('organizations.enabled') && $org) {
            if (!$user->organization_id) {
                $user->organization_id = $org->id;
                $user->save();
            }

            // Imposta il team per spatie/permission solo se le organizzazioni sono abilitate
            if (config('organizations.enabled') && $org) {
                app(PermissionRegistrar::class)->setPermissionsTeamId($org->id);
            } else {
                app(PermissionRegistrar::class)->setPermissionsTeamId(null);
            }

            $user->assignRole('admin');
        } else {
            // Se le organizzazioni sono disabilitate, assegna admin globale
            $user->assignRole('admin');
        }
    }
}
