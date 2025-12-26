<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignPokemonToExistingUsersSeeder extends Seeder
{
    /**
     * Assign Pokemon game to all existing users as default.
     */
    public function run(): void
    {
        $pokemonGameId = DB::table('games')->where('code', 'pokemon')->value('id');

        if (!$pokemonGameId) {
            $this->command->warn('Pokemon game not found. Run GameSeeder first.');
            return;
        }

        $users = DB::table('users')->pluck('id');

        foreach ($users as $userId) {
            DB::table('game_user')->updateOrInsert(
                ['user_id' => $userId, 'game_id' => $pokemonGameId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info("Pokemon game assigned to {$users->count()} users.");
    }
}
