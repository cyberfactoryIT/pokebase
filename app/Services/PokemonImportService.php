<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Game;

class PokemonImportService
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected int $pageSize;

    public function __construct()
    {
        $this->baseUrl = config('pokemon.base_url');
        $this->apiKey  = config('pokemon.api_key');
        $this->pageSize = (int) config('pokemon.page_size', 250);
    }

    public function importAllCards(?callable $output = null): void
    {
        // Trova il game Pokémon
        $gameId = DB::table('games')->where('code', 'pokemon')->value('id');

        if (!$gameId) {
            throw new \RuntimeException('Pokemon game not found in `games` table.');
        }

        $page = 1;
        $totalCount = null;
        $imported = 0;

        do {
            $response = Http::withHeaders($this->headers())
                ->timeout(90)              // aumenta timeout a 90 secondi
                ->retry(5, 2000)           // fino a 5 tentativi, attesa 2s tra i tentativi
                ->get("{$this->baseUrl}/cards", [
                    'page'     => $page,
                    'pageSize' => $this->pageSize,
                ]);


            if (!$response->successful()) {
                throw new \RuntimeException("Error calling Pokemon TCG API on page {$page}: " . $response->body());
            }

            $data = $response->json();

            $cards   = $data['data'] ?? [];
            $totalCount = $data['totalCount'] ?? null;

            if ($output) {
                $output("Page {$page} - received " . count($cards) . " cards");
            }

            DB::transaction(function () use ($cards, $gameId, &$imported) {
                foreach ($cards as $card) {
                    $this->upsertCard($card, $gameId);
                    $imported++;
                }
            });

            $page++;

            // Se non c'è data o è vuota, usciamo
        } while (!empty($cards));

        if ($output) {
            $output("Import finished. Total imported/updated: {$imported} cards.");
            if ($totalCount !== null) {
                $output("API totalCount reported: {$totalCount}");
            }
        }
    }

    protected function headers(): array
    {
        $headers = [
            'Accept' => 'application/json',
        ];

        if ($this->apiKey) {
            $headers['X-Api-Key'] = $this->apiKey;
        }

        return $headers;
    }

    protected function upsertCard(array $card, int $gameId): void
    {
        // Mapping dal JSON dell'API al tuo schema `card_catalog`
        $set = $card['set'] ?? [];

        $imageUrl = null;
        if (isset($card['images']['small'])) {
            $imageUrl = $card['images']['small'];
        } elseif (isset($card['images']['large'])) {
            $imageUrl = $card['images']['large'];
        }

        $extra = [
            'supertype'  => $card['supertype'] ?? null,
            'subtypes'   => $card['subtypes'] ?? null,
            'hp'         => $card['hp'] ?? null,
            'types'      => $card['types'] ?? null,
            'tcgplayer'  => $card['tcgplayer'] ?? null,
            'cardmarket' => $card['cardmarket'] ?? null,
            'legalities' => $card['legalities'] ?? null,
        ];

        DB::table('card_catalog')->updateOrInsert(
            [
                // chiave logica per non duplicare
                'game_id'         => $gameId,
                'set_code'        => $set['id'] ?? null,
                'collector_number'=> $card['number'] ?? null,
                'name'            => $card['name'] ?? null,
            ],
            [
                'game_id'         => $gameId,
                'name'            => $card['name'] ?? null,
                'set_name'        => $set['name'] ?? null,
                'set_code'        => $set['id'] ?? null,
                'collector_number'=> $card['number'] ?? null,
                'rarity'          => $card['rarity'] ?? null,
                'type_line'       => $card['supertype'] ?? null,
                'image_url'       => $imageUrl,
                'extra_data'      => json_encode($extra),
                'updated_at'      => now(),
                'created_at'      => now(), // in updateOrInsert, se già esiste verrà ignorato
            ]
        );
    }
}
