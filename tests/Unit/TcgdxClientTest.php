<?php

namespace Tests\Unit;

use App\Services\Tcgdx\TcgdxClient;
use Tests\TestCase;

class TcgdxClientTest extends TestCase
{
    public function test_normalize_set_handles_string_name(): void
    {
        $client = new TcgdxClient();
        
        $setData = [
            'id' => 'base1',
            'name' => 'Base Set', // String instead of array
            'serie' => ['name' => 'Base'],
            'releaseDate' => '1999-01-09',
        ];
        
        $normalized = $client->normalizeSet($setData);
        
        $this->assertEquals('base1', $normalized['tcgdex_id']);
        $this->assertEquals(['en' => 'Base Set'], $normalized['name']);
        $this->assertEquals('Base', $normalized['series']);
        $this->assertEquals('1999-01-09', $normalized['release_date']);
    }

    public function test_normalize_set_handles_multilingual_name(): void
    {
        $client = new TcgdxClient();
        
        $setData = [
            'id' => 'base1',
            'name' => [
                'en' => 'Base Set',
                'fr' => 'Édition de base',
                'de' => 'Basis-Set',
            ],
            'serie' => ['name' => 'Base'],
        ];
        
        $normalized = $client->normalizeSet($setData);
        
        $this->assertEquals(['en' => 'Base Set', 'fr' => 'Édition de base', 'de' => 'Basis-Set'], $normalized['name']);
    }

    public function test_normalize_card_handles_all_fields(): void
    {
        $client = new TcgdxClient();
        
        $cardData = [
            'id' => 'base1-1',
            'localId' => '1',
            'name' => ['en' => 'Alakazam'],
            'category' => 'Pokémon',
            'hp' => 80,
            'types' => ['Psychic'],
            'rarity' => 'Rare Holo',
            'illustrator' => 'Ken Sugimori',
            'image' => 'https://example.com/card.png',
            'evolveFrom' => 'Kadabra',
        ];
        
        $normalized = $client->normalizeCard($cardData, 1);
        
        $this->assertEquals('base1-1', $normalized['tcgdex_id']);
        $this->assertEquals(1, $normalized['set_tcgdx_id']);
        $this->assertEquals('1', $normalized['local_id']);
        $this->assertEquals(['en' => 'Alakazam'], $normalized['name']);
        $this->assertEquals('Pokémon', $normalized['supertype']);
        $this->assertEquals(80, $normalized['hp']);
        $this->assertEquals(['Psychic'], $normalized['types']);
        $this->assertEquals('Rare Holo', $normalized['rarity']);
        $this->assertEquals('Ken Sugimori', $normalized['illustrator']);
        $this->assertEquals('Kadabra', $normalized['evolves_from']);
    }

    public function test_normalize_card_handles_missing_optional_fields(): void
    {
        $client = new TcgdxClient();
        
        $cardData = [
            'id' => 'energy-1',
            'name' => 'Fire Energy',
            'category' => 'Energy',
        ];
        
        $normalized = $client->normalizeCard($cardData, 1);
        
        $this->assertEquals('energy-1', $normalized['tcgdex_id']);
        $this->assertNull($normalized['hp']);
        $this->assertNull($normalized['types']);
        $this->assertNull($normalized['rarity']);
        $this->assertNull($normalized['evolves_from']);
    }
}
