<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure Pokémon exists (idempotent)
        DB::table('games')->updateOrInsert(
            ['code' => 'pokemon'],
            [
                'name'               => 'Pokémon TCG',
                'tcgcsv_category_id' => 3, // Pokemon category in TCGCSV
                'is_active'          => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]
        );

        // Magic: The Gathering
        DB::table('games')->updateOrInsert(
            ['code' => 'mtg'],
            [
                'name'               => 'Magic: The Gathering',
                'tcgcsv_category_id' => 1, // MTG category in TCGCSV
                'is_active'          => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]
        );

        // Yu-Gi-Oh!
        DB::table('games')->updateOrInsert(
            ['code' => 'yugioh'],
            [
                'name'               => 'Yu-Gi-Oh!',
                'tcgcsv_category_id' => 2, // Yu-Gi-Oh! category in TCGCSV
                'is_active'          => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]
        );
    }
}
