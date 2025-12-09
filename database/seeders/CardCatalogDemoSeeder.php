<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Game;

class CardCatalogDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Prende il game Pokémon
        $pokemonGameId = DB::table('games')
            ->where('code', 'pokemon')
            ->value('id');

        if (!$pokemonGameId) {
            return;
        }

        $cards = [
            [
                'name'             => 'Pikachu',
                'set_name'         => 'Base Set',
                'set_code'         => 'BASE',
                'collector_number' => '58/102',
                'rarity'           => 'Common',
                'type_line'        => 'Basic Pokémon',
                'image_url'        => null,
            ],
            [
                'name'             => 'Charizard',
                'set_name'         => 'Base Set',
                'set_code'         => 'BASE',
                'collector_number' => '4/102',
                'rarity'           => 'Rare Holo',
                'type_line'        => 'Stage 2 Pokémon',
                'image_url'        => null,
            ],
            [
                'name'             => 'Squirtle',
                'set_name'         => 'Base Set',
                'set_code'         => 'BASE',
                'collector_number' => '63/102',
                'rarity'           => 'Common',
                'type_line'        => 'Basic Pokémon',
                'image_url'        => null,
            ],
            [
                'name'             => 'Bulbasaur',
                'set_name'         => 'Base Set',
                'set_code'         => 'BASE',
                'collector_number' => '44/102',
                'rarity'           => 'Common',
                'type_line'        => 'Basic Pokémon',
                'image_url'        => null,
            ],
            [
                'name'             => 'Mewtwo',
                'set_name'         => 'Base Set',
                'set_code'         => 'BASE',
                'collector_number' => '10/102',
                'rarity'           => 'Rare',
                'type_line'        => 'Basic Pokémon',
                'image_url'        => null,
            ],
        ];

        foreach ($cards as $card) {
            DB::table('card_catalog')->updateOrInsert(
                [
                    'game_id'         => $pokemonGameId,
                    'name'            => $card['name'],
                    'set_code'        => $card['set_code'],
                    'collector_number'=> $card['collector_number'],
                ],
                array_merge($card, [
                    'game_id'    => $pokemonGameId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
