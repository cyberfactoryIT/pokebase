<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure Pokémon exists (idempotent)
        $existing = DB::table('games')->where('code', 'pokemon')->first();

        if (!$existing) {
            DB::table('games')->insert([
                'name'       => 'Pokémon TCG',
                'code'       => 'pokemon',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Here you can add other games in the future, e.g.:
        // DB::table('games')->updateOrInsert(
        //     ['code' => 'mtg'],
        //     ['name' => 'Magic: The Gathering', 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
        // );
    }
}
