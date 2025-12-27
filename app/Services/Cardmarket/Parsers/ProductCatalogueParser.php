<?php

namespace App\Services\Cardmarket\Parsers;

use Generator;
use Illuminate\Support\Facades\Log;

class ProductCatalogueParser
{
    protected array $mapping;

    public function __construct()
    {
        $this->mapping = config('cardmarket.mapping.products');
    }

    /**
     * Parse the products JSON file and yield rows.
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
        if (!isset($data['products']) || !is_array($data['products'])) {
            throw new \RuntimeException("Invalid JSON structure: missing 'products' array");
        }

        $version = $data['version'] ?? null;
        $createdAt = $data['createdAt'] ?? null;

        Log::channel(config('cardmarket.logging.channel'))->info("Parsing products JSON", [
            'version' => $version,
            'created_at' => $createdAt,
            'product_count' => count($data['products']),
        ]);

        // Yield each product as normalized array
        foreach ($data['products'] as $index => $product) {
            try {
                $normalized = $this->normalizeProduct($product);
                if ($normalized) {
                    yield $normalized;
                }
            } catch (\Exception $e) {
                Log::channel(config('cardmarket.logging.channel'))->warning("Failed to parse product", [
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'product' => $product,
                ]);
            }
        }
    }

    /**
     * Normalize a product object to database fields.
     *
     * @param array $product
     * @return array|null
     */
    protected function normalizeProduct(array $product): ?array
    {
        // Validate required field
        if (empty($product[$this->mapping['cardmarket_product_id']])) {
            return null;
        }

        $data = [];

        // Map all fields according to config
        foreach ($this->mapping as $dbField => $jsonField) {
            $value = $product[$jsonField] ?? null;
            
            // Handle specific field types
            if ($dbField === 'cardmarket_product_id' || str_starts_with($dbField, 'id_')) {
                $data[$dbField] = $value !== null ? (int) $value : null;
            } elseif ($dbField === 'date_added' && $value) {
                // Handle invalid MySQL dates like '0000-00-00 00:00:00'
                if ($value === '0000-00-00 00:00:00' || $value === '0000-00-00') {
                    $data[$dbField] = null;
                } else {
                    $data[$dbField] = $value;
                }
            } else {
                $data[$dbField] = $value;
            }
        }

        // Store complete original JSON for reference
        $data['raw'] = json_encode($product);

        return $data;
    }
}
