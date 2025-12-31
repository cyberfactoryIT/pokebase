<?php

namespace Tests\Feature;

use App\Models\Tcgdx\TcgdxCard;
use App\Models\Tcgdx\TcgdxImportRun;
use App\Models\Tcgdx\TcgdxSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TcgdxImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_command_creates_sets_and_cards(): void
    {
        // Mock TCGdex API responses
        Http::fake([
            '*/en/sets' => Http::response([
                [
                    'id' => 'base1',
                    'name' => ['en' => 'Base Set'],
                ],
                [
                    'id' => 'jungle',
                    'name' => ['en' => 'Jungle'],
                ],
            ], 200),

            '*/en/sets/base1' => Http::response([
                'id' => 'base1',
                'name' => ['en' => 'Base Set'],
                'serie' => ['name' => 'Base'],
                'logo' => 'https://example.com/logo.png',
                'symbol' => 'https://example.com/symbol.png',
                'releaseDate' => '1999-01-09',
                'cardCount' => [
                    'total' => 102,
                    'official' => 102,
                ],
                'cards' => [
                    [
                        'id' => 'base1-1',
                        'localId' => '1',
                        'name' => ['en' => 'Alakazam'],
                        'image' => 'https://example.com/card1.png',
                        'category' => 'Pokémon',
                        'hp' => 80,
                        'types' => ['Psychic'],
                        'rarity' => 'Rare Holo',
                    ],
                    [
                        'id' => 'base1-2',
                        'localId' => '2',
                        'name' => ['en' => 'Blastoise'],
                        'image' => 'https://example.com/card2.png',
                        'category' => 'Pokémon',
                        'hp' => 100,
                        'types' => ['Water'],
                        'rarity' => 'Rare Holo',
                    ],
                ],
            ], 200),

            '*/en/sets/jungle' => Http::response([
                'id' => 'jungle',
                'name' => ['en' => 'Jungle'],
                'serie' => ['name' => 'Base'],
                'releaseDate' => '1999-06-16',
                'cardCount' => [
                    'total' => 64,
                    'official' => 64,
                ],
                'cards' => [
                    [
                        'id' => 'jungle-1',
                        'localId' => '1',
                        'name' => ['en' => 'Clefable'],
                        'image' => 'https://example.com/jungle1.png',
                        'category' => 'Pokémon',
                    ],
                ],
            ], 200),
        ]);

        // Run import command
        $this->artisan('tcgdx:import')
            ->assertExitCode(0);

        // Verify data was imported
        $this->assertDatabaseCount('tcgdx_sets', 2);
        $this->assertDatabaseCount('tcgdx_cards', 3);
        $this->assertDatabaseCount('tcgdx_import_runs', 1);

        // Verify set data
        $baseSet = TcgdxSet::where('tcgdex_id', 'base1')->first();
        $this->assertNotNull($baseSet);
        $this->assertEquals('Base Set', $baseSet->name['en']);
        $this->assertEquals('Base', $baseSet->series);
        $this->assertEquals(102, $baseSet->card_count_total);
        $this->assertEquals('1999-01-09', $baseSet->release_date->format('Y-m-d'));

        // Verify card data
        $alakazam = TcgdxCard::where('tcgdex_id', 'base1-1')->first();
        $this->assertNotNull($alakazam);
        $this->assertEquals('Alakazam', $alakazam->name['en']);
        $this->assertEquals($baseSet->id, $alakazam->set_tcgdx_id);
        $this->assertEquals('1', $alakazam->local_id);
        $this->assertEquals(80, $alakazam->hp);
        $this->assertEquals(['Psychic'], $alakazam->types);

        // Verify relationships
        $this->assertEquals(2, $baseSet->cards()->count());

        // Verify import run
        $run = TcgdxImportRun::first();
        $this->assertEquals('success', $run->status);
        $this->assertEquals(2, $run->stats['sets_imported']);
        $this->assertEquals(3, $run->stats['cards_total']);
    }

    public function test_import_single_set(): void
    {
        Http::fake([
            '*/en/sets/base1' => Http::response([
                'id' => 'base1',
                'name' => ['en' => 'Base Set'],
                'serie' => ['name' => 'Base'],
                'cardCount' => ['total' => 1, 'official' => 1],
                'cards' => [
                    [
                        'id' => 'base1-1',
                        'localId' => '1',
                        'name' => ['en' => 'Pikachu'],
                        'category' => 'Pokémon',
                    ],
                ],
            ], 200),
        ]);

        $this->artisan('tcgdx:import --set=base1')
            ->assertExitCode(0);

        $this->assertDatabaseCount('tcgdx_sets', 1);
        $this->assertDatabaseCount('tcgdx_cards', 1);
    }

    public function test_import_is_idempotent(): void
    {
        Http::fake([
            '*/en/sets/base1' => Http::response([
                'id' => 'base1',
                'name' => ['en' => 'Base Set'],
                'cardCount' => ['total' => 1, 'official' => 1],
                'cards' => [
                    ['id' => 'base1-1', 'localId' => '1', 'name' => ['en' => 'Test']],
                ],
            ], 200),
        ]);

        // Import twice
        $this->artisan('tcgdx:import --set=base1');
        $this->artisan('tcgdx:import --set=base1');

        // Should still have only 1 set and 1 card
        $this->assertDatabaseCount('tcgdx_sets', 1);
        $this->assertDatabaseCount('tcgdx_cards', 1);
    }
}
