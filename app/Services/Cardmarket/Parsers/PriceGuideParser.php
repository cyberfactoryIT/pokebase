<?php

namespace App\Services\Cardmarket\Parsers;

use Generator;
use Illuminate\Support\Facades\Log;

class PriceGuideParser
{
    protected array $mapping;

    public function __construct()
    {
        $this->mapping = config('cardmarket.mapping.prices');
    }

    /**
     * Parse the price guide JSON file and yield rows.
     *
     * @param string $jsonPath Full filesystem path to JSON
     * @return Generator
     */
    public function parse(string $jsonPath): Generator
    {
        if (!file_exists($jsonPath)) {
            throw new \InvalidArgumentException("JSON file not found: {$jsonPath}");
        }

        $content = file_get_contents($jsonPath);
        if ($content === false) {
            throw new \RuntimeException("Failed to read JSON file: {$jsonPath}");
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException("Failed to parse JSON: {$e->getMessage()}");
        }

        // Validate structure
        if (!isset($data['priceGuides']) || !is_array($data['priceGuides'])) {
            throw new \RuntimeException("Invalid JSON structure: missing 'priceGuides' array");
        }

        $version = $data['version'] ?? null;
        $createdAt = $data['createdAt'] ?? null;

        Log::channel(config('cardmarket.logging.channel'))->info("Parsing price guide JSON", [
            'version' => $version,
            'created_at' => $createdAt,
            'price_count' => count($data['priceGuides']),
        ]);

        // Yield each price as normalized array
        foreach ($data['priceGuides'] as $index => $price) {
            try {
                $normalized = $this->normalizePrice($price);
                if ($normalized) {
                    yield $normalized;
                }
            } catch (\Exception $e) {
                Log::channel(config('cardmarket.logging.channel'))->warning("Failed to parse price", [
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'price' => $price,
                ]);
            }
        }
    }

    /**
     * Normalize a price object to database fields.
     *
     * @param array $price
     * @return array|null
     */
    protected function normalizePrice(array $price): ?array
    {
        // Validate required field
        if (empty($price[$this->mapping['cardmarket_product_id']])) {
            return null;
        }

        $data = [];

        // Map all fields according to config
        foreach ($this->mapping as $dbField => $jsonField) {
            $value = $price[$jsonField] ?? null;
            
            // Handle specific field types
            if ($dbField === 'cardmarket_product_id' || $dbField === 'id_category') {
                $data[$dbField] = $value !== null ? (int) $value : null;
            } elseif (in_array($dbField, ['avg', 'low', 'trend', 'avg_holo', 'low_holo', 'trend_holo', 'avg1', 'avg7', 'avg30'])) {
                $data[$dbField] = $value !== null ? (float) $value : null;
            } else {
                $data[$dbField] = $value;
            }
        }

        // Store complete original JSON for reference
        $data['raw'] = json_encode($price);

        return $data;
    }
}
