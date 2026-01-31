<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $games = [
            [
                'name' => 'Pokémon TCG',
                'code' => 'pokemon',
                'slug' => 'pokemon',
                'catalog_backend' => 'tcgdex',
                'tcgcsv_category_id' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Magic: The Gathering',
                'code' => 'mtg',
                'slug' => 'magic-the-gathering',
                'catalog_backend' => 'tcgcsv',
                'tcgcsv_category_id' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Yu-Gi-Oh!',
                'code' => 'yugioh',
                'slug' => 'yu-gi-oh',
                'catalog_backend' => 'tcgcsv',
                'tcgcsv_category_id' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Disney Lorcana',
                'code' => 'lorcana',
                'slug' => 'lorcana',
                'catalog_backend' => 'cmapi',
                'tcgcsv_category_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'One Piece Card Game',
                'code' => 'onepiece',
                'slug' => 'onepiece',
                'catalog_backend' => 'cmapi',
                'tcgcsv_category_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($games as $game) {
            DB::table('games')->updateOrInsert(
                ['code' => $game['code']],
                $game
            );
        }

        $this->command->info('✓ Games seeded successfully!');
    }
}
