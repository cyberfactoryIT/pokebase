<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\Game;
use App\Models\PokemonImportLog;

class PokemonImportService
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected int $pageSize;
    protected int $batchSize = 50; // Ridotto da 100 a 50 per evitare timeout
    protected int $maxRetries = 5;
    protected int $retryDelay = 3000; // milliseconds
    protected ?PokemonImportLog $importLog = null;

    public function __construct()
    {
        $this->baseUrl = config('pokemon.base_url');
        $this->apiKey  = config('pokemon.api_key');
        $this->pageSize = (int) config('pokemon.page_size', 250);
    }

    public function importAllCards(
        ?callable $output = null, 
        int $fromPage = 1,
        ?string $setCode = null,
        bool $force = false
    ): array
    {
        // Crea log di import
        $batchId = 'import_' . now()->format('Ymd_His') . '_' . Str::random(6);
        $this->importLog = PokemonImportLog::create([
            'batch_id' => $batchId,
            'set_code' => $setCode,
            'start_page' => $fromPage,
            'status' => 'started',
            'started_at' => now(),
            'pages_completed' => [],
        ]);

        if ($output) {
            $output("🆔 Import Batch ID: {$batchId}");
        }

        try {
            $stats = $this->performImport($output, $fromPage, $setCode, $force);
            
            // Marca come completato
            $this->importLog->markCompleted();
            
            return array_merge($stats, [
                'batch_id' => $batchId,
                'duration' => $this->importLog->duration,
            ]);
            
        } catch (\Throwable $e) {
            // Marca come fallito
            $this->importLog->markFailed($e->getMessage());
            throw $e;
        }
    }

    protected function performImport(
        ?callable $output,
        int $fromPage,
        ?string $setCode,
        bool $force
    ): array
    {
        $startTime = now();
        
        // Trova il game Pokémon
        $gameId = DB::table('games')->where('code', 'pokemon')->value('id');

        if (!$gameId) {
            throw new \RuntimeException('Pokemon game not found in `games` table.');
        }

        // Aggiorna status
        $this->importLog->status = 'in_progress';
        $this->importLog->save();

        $page = $fromPage;
        $totalCount = null;
        $stats = [
            'processed' => 0,
            'new' => 0,
            'updated' => 0,
            'failed' => 0,
        ];
        
        $cardsBuffer = [];

        do {
            try {
                $params = [
                    'page'     => $page,
                    'pageSize' => $this->pageSize,
                ];
                
                if ($setCode) {
                    $params['q'] = "set.id:{$setCode}";
                }
                
                $response = $this->makeRequestWithRetry($params, $page, $output);
                
                if (!$response) {
                    if ($output) {
                        $output("⚠️  Skipping page {$page} after all retry attempts failed");
                    }
                    Log::warning("Page {$page} skipped after all retries", [
                        'batch_id' => $this->importLog->batch_id,
                    ]);
                    $page++;
                    continue;
                }

                $data = $response->json();
                $cards = $data['data'] ?? [];
                $totalCount = $data['totalCount'] ?? null;
                
                // Aggiorna total_pages se disponibile
                if ($totalCount && !$this->importLog->total_pages) {
                    $totalPages = ceil($totalCount / $this->pageSize);
                    $this->importLog->total_pages = $totalPages;
                    $this->importLog->save();
                }

                if (empty($cards)) {
                    break;
                }

                if ($output) {
                    $progressInfo = "";
                    if ($this->importLog->total_pages) {
                        $progressInfo = " [{$page}/{$this->importLog->total_pages}]";
                    }
                    $output("📄 Page {$page}{$progressInfo} - received " . count($cards) . " cards");
                }

                // Accumula le carte nel buffer
                foreach ($cards as $card) {
                    $cardsBuffer[] = [
                        'card' => $card,
                        'gameId' => $gameId,
                    ];
                    
                    // Quando il buffer raggiunge la dimensione del batch, processa
                    if (count($cardsBuffer) >= $this->batchSize) {
                        $batchStats = $this->processBatch($cardsBuffer, $force);
                        $stats['new'] += $batchStats['new'];
                        $stats['updated'] += $batchStats['updated'];
                        $stats['failed'] += $batchStats['failed'];
                        $stats['processed'] += count($cardsBuffer);
                        
                        // Aggiorna il log
                        $this->importLog->updateStats([
                            'processed' => count($cardsBuffer),
                            'new' => $batchStats['new'],
                            'updated' => $batchStats['updated'],
                            'failed' => $batchStats['failed'],
                            'failedCards' => $batchStats['failedCards'],
                        ]);
                        
                        $cardsBuffer = [];
                    }
                }
                
                // Marca la pagina come completata
                $this->importLog->markPageCompleted($page);
                
                if ($output && $this->importLog->progress_percentage !== null) {
                    $output("   Progress: {$this->importLog->progress_percentage}% complete");
                }

                $page++;
                
                // Pausa molto lunga per API instabile (2 secondi)
                sleep(2);

            } catch (\Throwable $e) {
                Log::error("Error processing page {$page}: " . $e->getMessage(), [
                    'exception' => $e,
                    'page' => $page,
                    'batch_id' => $this->importLog->batch_id,
                ]);
                
                if ($output) {
                    $output("❌ Error on page {$page}: " . $e->getMessage());
                    $output("Continuing to next page...");
                }
                
                $page++;
                sleep(2); // Pausa più lunga dopo un errore
            }

        } while (true);

        // Processa le carte rimanenti nel buffer
        if (!empty($cardsBuffer)) {
            $batchStats = $this->processBatch($cardsBuffer, $force);
            $stats['new'] += $batchStats['new'];
            $stats['updated'] += $batchStats['updated'];
            $stats['failed'] += $batchStats['failed'];
            $stats['processed'] += count($cardsBuffer);
            
            $this->importLog->updateStats([
                'processed' => count($cardsBuffer),
                'new' => $batchStats['new'],
                'updated' => $batchStats['updated'],
                'failed' => $batchStats['failed'],
                'failedCards' => $batchStats['failedCards'],
            ]);
        }

        if ($output) {
            $output("✅ Import finished!");
            $output("   Processed: {$stats['processed']} cards");
            $output("   New: {$stats['new']} | Updated: {$stats['updated']} | Failed: {$stats['failed']}");
            if ($totalCount !== null) {
                $output("   API reported total: {$totalCount}");
            }
        }
        
        return $stats;
    }

    protected function makeRequestWithRetry(array $params, int $page, ?callable $output = null)
    {
        $attempt = 0;
        $lastException = null;

        // Aumenta i limiti PHP
        set_time_limit(120); // 2 minuti per richiesta
        ini_set('max_execution_time', '120');

        while ($attempt < $this->maxRetries) {
            try {
                // Usa exec curl diretto come nel browser
                $url = "{$this->baseUrl}/cards?" . http_build_query($params);
                
                $curlCmd = sprintf(
                    'curl -s -H "Accept: application/json" -H "X-Api-Key: %s" "%s"',
                    escapeshellarg($this->apiKey),
                    escapeshellarg($url)
                );
                
                $json = shell_exec($curlCmd);
                
                if ($json === null || $json === false || empty($json)) {
                    throw new \RuntimeException("Failed to fetch data from API via curl");
                }
                
                // Decodifica JSON
                $data = json_decode($json, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException("Invalid JSON response: " . json_last_error_msg());
                }
                
                // Ritorna un oggetto compatibile
                return new class($data) {
                    private $data;
                    public function __construct($data) { $this->data = $data; }
                    public function successful() { return true; }
                    public function json() { return $this->data; }
                };
                
                /* Vecchio metodo Guzzle che non funziona
                $response = Http::withHeaders($this->headers())
                    ->timeout(30)
                    ->connectTimeout(10)
                    ->get("{$this->baseUrl}/cards", $params);

                if ($response->successful()) {
                    return $response;
                }

                // Se non è successful, logga e ritenta
                Log::warning("API returned non-200 status on page {$page}, attempt " . ($attempt + 1), [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                */

            } catch (\Throwable $e) {
                $lastException = $e;
                Log::warning("Request failed on page {$page}, attempt " . ($attempt + 1) . ": " . $e->getMessage());
            }

            $attempt++;
            
            if ($attempt < $this->maxRetries) {
                // Exponential backoff: 3s, 6s, 12s, 24s, 48s
                $delay = $this->retryDelay * pow(2, $attempt - 1);
                
                if ($output) {
                    $output("⏳ Retry {$attempt}/{$this->maxRetries} after " . ($delay / 1000) . "s...");
                }
                
                usleep($delay * 1000);
            }
        }

        // Se arriviamo qui, tutti i tentativi sono falliti
        if ($lastException) {
            Log::error("All retry attempts failed for page {$page}", [
                'exception' => $lastException,
            ]);
        }

        return null;
    }

    protected function processBatch(array $cardsBuffer, bool $force): array
    {
        $stats = [
            'new' => 0,
            'updated' => 0,
            'failed' => 0,
            'failedCards' => [],
        ];

        DB::transaction(function () use ($cardsBuffer, $force, &$stats) {
            foreach ($cardsBuffer as $item) {
                try {
                    $result = $this->upsertCard($item['card'], $item['gameId'], $force);
                    
                    if ($result['action'] === 'insert') {
                        $stats['new']++;
                    } elseif ($result['action'] === 'update') {
                        $stats['updated']++;
                    }
                    
                } catch (\Throwable $e) {
                    $stats['failed']++;
                    $stats['failedCards'][] = [
                        'name' => $item['card']['name'] ?? 'Unknown',
                        'id' => $item['card']['id'] ?? 'Unknown',
                        'error' => $e->getMessage(),
                    ];
                    
                    Log::error('Failed to import card', [
                        'card' => $item['card'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        return $stats;
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

    protected function upsertCard(array $card, int $gameId, bool $force = false): array
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

        // Verifica se la carta esiste già
        $existing = DB::table('card_catalog')
            ->where('game_id', $gameId)
            ->where('set_code', $set['id'] ?? null)
            ->where('collector_number', $card['number'] ?? null)
            ->where('name', $card['name'] ?? null)
            ->first();

        $data = [
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
        ];

        if ($existing && !$force) {
            // Aggiorna solo se force è false e la carta esiste
            DB::table('card_catalog')
                ->where('id', $existing->id)
                ->update($data);
                
            return ['action' => 'update', 'id' => $existing->id];
        } else {
            // Inserisci nuova carta o sovrascrive se force è true
            $data['created_at'] = now();
            
            if ($existing && $force) {
                DB::table('card_catalog')
                    ->where('id', $existing->id)
                    ->update($data);
                    
                return ['action' => 'update', 'id' => $existing->id];
            } else {
                $id = DB::table('card_catalog')->insertGetId($data);
                return ['action' => 'insert', 'id' => $id];
            }
        }
    }
}
