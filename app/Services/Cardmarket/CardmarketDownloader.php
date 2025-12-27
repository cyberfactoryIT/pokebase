<?php

namespace App\Services\Cardmarket;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CardmarketDownloader
{
    protected string $logChannel;
    protected array $storage;

    public function __construct()
    {
        $this->logChannel = config('cardmarket.logging.channel', 'cardmarket');
        $this->storage = config('cardmarket.storage');
    }

    /**
     * Download product and price files for a specific game.
     *
     * @param string $game Game key from config (e.g., 'pokemon', 'mtg', 'yugioh')
     * @param bool $force Force download even if cached
     * @return array ['products' => array, 'prices' => array]
     */
    public function downloadGame(string $game, bool $force = false): array
    {
        $gameConfig = config("cardmarket.games.{$game}");

        if (!$gameConfig) {
            throw new \InvalidArgumentException("Game '{$game}' not configured");
        }

        Log::channel($this->logChannel)->info("Downloading {$game} data", ['game_id' => $gameConfig['id']]);

        $products = $this->downloadProducts($game, $force);
        $prices = $this->downloadPrices($game, $force);

        return [
            'products' => $products,
            'prices' => $prices,
        ];
    }

    /**
     * Download the product catalogue JSON for a game.
     *
     * @param string $game
     * @param bool $force Force download even if cached
     * @return array ['success' => bool, 'path' => string|null, 'version' => string|null, 'message' => string]
     */
    public function downloadProducts(string $game, bool $force = false): array
    {
        $gameConfig = config("cardmarket.games.{$game}");
        
        if (!$gameConfig || empty($gameConfig['products_url'])) {
            return [
                'success' => false,
                'path' => null,
                'version' => null,
                'message' => "Products URL not configured for game '{$game}'",
            ];
        }

        $url = $gameConfig['products_url'];
        Log::channel($this->logChannel)->info("Starting products download for {$game}", ['url' => $url]);

        return $this->downloadJson($url, $game, 'products', $force);
    }

    /**
     * Download the price guide JSON for a game.
     *
     * @param string $game
     * @param bool $force Force download even if cached
     * @return array ['success' => bool, 'path' => string|null, 'version' => string|null, 'message' => string]
     */
    public function downloadPrices(string $game, bool $force = false): array
    {
        $gameConfig = config("cardmarket.games.{$game}");
        
        if (!$gameConfig || empty($gameConfig['prices_url'])) {
            return [
                'success' => false,
                'path' => null,
                'version' => null,
                'message' => "Prices URL not configured for game '{$game}'",
            ];
        }

        $url = $gameConfig['prices_url'];
        Log::channel($this->logChannel)->info("Starting prices download for {$game}", ['url' => $url]);

        return $this->downloadJson($url, $game, 'prices', $force);
    }

    /**
     * Download a JSON file from URL.
     *
     * @param string $url
     * @param string $game
     * @param string $type 'products' or 'prices'
     * @param bool $force
     * @return array
     */
    protected function downloadJson(string $url, string $game, string $type, bool $force): array
    {
        try {
            $filename = "{$game}_{$type}_" . date('Ymd_His') . '.json';
            $rawPath = $this->storage['raw'] . '/' . $filename;
            $fullPath = Storage::path($rawPath);

            // Check if we have a recent cached version
            if (!$force && $this->hasCachedVersion($game, $type)) {
                Log::channel($this->logChannel)->info("Using cached {$game} {$type} file");
                $cachedPath = $this->getLatestFile($game, $type);
                
                if ($cachedPath) {
                    return [
                        'success' => true,
                        'path' => $cachedPath,
                        'storage_path' => $this->getStoragePath($cachedPath),
                        'version' => $this->getFileVersion($cachedPath),
                        'message' => "Using cached {$game} {$type} file",
                        'cached' => true,
                    ];
                }
            }

            // Ensure directory exists
            Storage::makeDirectory($this->storage['raw']);

            // Download file
            Log::channel($this->logChannel)->info("Downloading {$game} {$type} from {$url}");
            
            $response = Http::timeout(300)->get($url);

            if (!$response->successful()) {
                throw new \Exception("Failed to download {$game} {$type}: HTTP {$response->status()}");
            }

            // Validate JSON
            $jsonData = $response->json();
            if (!$jsonData) {
                throw new \Exception("Invalid JSON response from {$url}");
            }

            // Save to storage
            Storage::put($rawPath, $response->body());
            
            $fileSize = Storage::size($rawPath);
            $fileHash = md5_file($fullPath);

            Log::channel($this->logChannel)->info("Downloaded {$game} {$type}", [
                'size' => $fileSize,
                'hash' => $fileHash,
                'path' => $rawPath,
                'version' => $jsonData['version'] ?? null,
                'created_at' => $jsonData['createdAt'] ?? null,
                'item_count' => count($jsonData[$type] ?? $jsonData['priceGuides'] ?? []),
            ]);

            return [
                'success' => true,
                'path' => $fullPath,
                'storage_path' => $rawPath,
                'version' => $fileHash,
                'json_version' => $jsonData['version'] ?? null,
                'created_at' => $jsonData['createdAt'] ?? null,
                'message' => "Successfully downloaded {$game} {$type}",
                'size' => $fileSize,
                'hash' => $fileHash,
                'cached' => false,
            ];

        } catch (\Exception $e) {
            Log::channel($this->logChannel)->error("Failed to download {$game} {$type}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'path' => null,
                'version' => null,
                'message' => "Error downloading {$game} {$type}: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if a recent cached version exists.
     *
     * @param string $game
     * @param string $type
     * @return bool
     */
    protected function hasCachedVersion(string $game, string $type): bool
    {
        $cacheDuration = config('cardmarket.import.cache_duration', 24);
        $files = Storage::files($this->storage['raw']);
        
        foreach ($files as $file) {
            if (str_contains($file, "{$game}_{$type}") && Storage::lastModified($file) > (time() - $cacheDuration * 3600)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the latest file for a game and type.
     *
     * @param string $game
     * @param string $type
     * @return string|null Full filesystem path
     */
    protected function getLatestFile(string $game, string $type): ?string
    {
        $files = Storage::files($this->storage['raw']);
        $matchingFiles = [];

        foreach ($files as $file) {
            if (str_contains($file, "{$game}_{$type}")) {
                $matchingFiles[$file] = Storage::lastModified($file);
            }
        }

        if (empty($matchingFiles)) {
            return null;
        }

        arsort($matchingFiles);
        $latestFile = array_key_first($matchingFiles);

        return Storage::path($latestFile);
    }

    /**
     * Get storage path from full filesystem path.
     *
     * @param string $fullPath
     * @return string
     */
    protected function getStoragePath(string $fullPath): string
    {
        $storagePath = Storage::path('');
        return str_replace($storagePath, '', $fullPath);
    }

    /**
     * Get version identifier for a file (MD5 hash).
     *
     * @param string $fullPath
     * @return string
     */
    protected function getFileVersion(string $fullPath): string
    {
        return md5_file($fullPath);
    }
}
