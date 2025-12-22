<?php

namespace App\Services\Tcgcsv;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TcgcsvClient
{
    protected string $baseUrl;
    protected int $categoryId;
    protected int $timeout;
    protected array $retryConfig;
    
    public function __construct()
    {
        $this->baseUrl = config('tcgcsv.base_url');
        $this->categoryId = config('tcgcsv.category_id');
        $this->timeout = config('tcgcsv.timeout');
        $this->retryConfig = config('tcgcsv.retry');
    }
    
    /**
     * Get all groups for the Pokemon category
     */
    public function getGroups(): array
    {
        $url = "{$this->baseUrl}/{$this->categoryId}/groups";
        return $this->makeRequest($url);
    }
    
    /**
     * Get all products for a specific group
     */
    public function getProducts(int $groupId): array
    {
        $url = "{$this->baseUrl}/{$this->categoryId}/{$groupId}/products";
        return $this->makeRequest($url);
    }
    
    /**
     * Get all prices for a specific group
     */
    public function getPrices(int $groupId): array
    {
        $url = "{$this->baseUrl}/{$this->categoryId}/{$groupId}/prices";
        return $this->makeRequest($url);
    }
    
    /**
     * Make HTTP request with retry logic
     */
    protected function makeRequest(string $url): array
    {
        $times = $this->retryConfig['times'];
        $sleep = $this->retryConfig['sleep'];
        $multiplier = $this->retryConfig['backoff_multiplier'];
        
        $attempt = 0;
        
        while ($attempt < $times) {
            try {
                $response = Http::timeout($this->timeout)
                    ->get($url);
                
                if ($response->successful()) {
                    return $response->json() ?? [];
                }
                
                // Retry on specific status codes
                if ($this->shouldRetry($response->status())) {
                    $attempt++;
                    
                    if ($attempt < $times) {
                        $sleepTime = $sleep * pow($multiplier, $attempt - 1);
                        // Add jitter (0-20% random variation)
                        $jitter = rand(0, (int)($sleepTime * 0.2));
                        $sleepTime += $jitter;
                        
                        Log::warning("TCGCSV API request failed (attempt {$attempt}/{$times})", [
                            'url' => $url,
                            'status' => $response->status(),
                            'retry_in_ms' => $sleepTime,
                        ]);
                        
                        usleep($sleepTime * 1000); // Convert to microseconds
                        continue;
                    }
                }
                
                throw new \Exception("TCGCSV API returned status {$response->status()}: {$response->body()}");
                
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $attempt++;
                
                if ($attempt < $times) {
                    $sleepTime = $sleep * pow($multiplier, $attempt - 1);
                    $jitter = rand(0, (int)($sleepTime * 0.2));
                    $sleepTime += $jitter;
                    
                    Log::warning("TCGCSV API connection failed (attempt {$attempt}/{$times})", [
                        'url' => $url,
                        'error' => $e->getMessage(),
                        'retry_in_ms' => $sleepTime,
                    ]);
                    
                    usleep($sleepTime * 1000);
                    continue;
                }
                
                throw $e;
            }
        }
        
        throw new \Exception("TCGCSV API request failed after {$times} attempts");
    }
    
    /**
     * Determine if request should be retried based on status code
     */
    protected function shouldRetry(int $status): bool
    {
        return in_array($status, [408, 429, 500, 502, 503, 504]);
    }
}
