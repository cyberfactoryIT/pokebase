# TCGCSV Import Pipeline

This is a **separate** import pipeline for TCGplayer Pokemon data via tcgcsv.com. It does NOT interfere with the existing pokemontcg.io pipeline.

## Overview

- **Source**: https://tcgcsv.com (TCGplayer API)
- **Category**: Pokemon (categoryId = 3)
- **Tables**: `tcgcsv_groups`, `tcgcsv_products`, `tcgcsv_prices`
- **Models**: `TcgcsvGroup`, `TcgcsvProduct`, `TcgcsvPrice`
- **Namespace**: `App\Services\Tcgcsv\`

## Configuration

Add these to your `.env` file:

```env
TCGCSV_BASE_URL=https://tcgcsv.com/tcgplayer
TCGCSV_CATEGORY_ID=3
TCGCSV_TIMEOUT=30
TCGCSV_RETRY_TIMES=3
TCGCSV_RETRY_SLEEP=2000
TCGCSV_CHUNK_SIZE=50
```

## Database Tables

### tcgcsv_groups
Stores Pokemon TCG sets/expansions from TCGplayer.
- `group_id` (unique): TCGplayer group identifier
- `name`: Set name (e.g., "Base Set")
- `abbreviation`: Short code
- `published_on`, `modified_on`: Timestamps from API
- `raw`: Full JSON payload

### tcgcsv_products
Stores individual Pokemon cards.
- `product_id` (unique): TCGplayer product identifier
- `group_id`: FK to group
- `name`: Card name
- `card_number`: Collector number (extracted from extendedData)
- `rarity`: Card rarity (extracted from extendedData)
- `image_url`: Card image
- `extended_data`: Additional metadata from API
- `raw`: Full JSON payload

### tcgcsv_prices
Stores pricing data with history support.
- `product_id`: FK to product
- `printing`: Normal/Holofoil/Reverse etc.
- `condition`: If provided by API
- `market_price`, `low_price`, `mid_price`, `high_price`, `direct_low_price`
- `snapshot_at`: When this price was recorded
- `raw`: Full JSON payload
- Unique key: `(product_id, printing, condition, snapshot_at)`

## Usage

### Import Everything (all groups + products + prices)

```bash
php artisan tcgcsv:import-pokemon
```

### Import Only Groups

```bash
php artisan tcgcsv:import-pokemon --only=groups
```

### Import Products for Specific Group

```bash
php artisan tcgcsv:import-pokemon --groupId=123 --only=products
```

### Import Prices for Specific Group

```bash
php artisan tcgcsv:import-pokemon --groupId=123 --only=prices
```

### Import Everything for One Group

```bash
php artisan tcgcsv:import-pokemon --groupId=123
```

## Features

### Retry Logic
- Retries on 408, 429, 500, 502, 503, 504 status codes
- Exponential backoff with jitter (default: 2s, 4s, 8s)
- Configurable via `config/tcgcsv.php`

### Idempotent Imports
- Uses `updateOrCreate` with unique keys
- Safe to run multiple times
- Won't create duplicates

### Logging
- Each import run has unique ID (e.g., `tcgcsv_20251222_143052_aB3x9z`)
- Logs to Laravel log with context
- Tracks new/updated/failed counts per entity type

### Data Parsing
- Extracts `card_number` from extendedData (matches: "Number", "Card Number", "Collector Number")
- Extracts `rarity` from extendedData
- Stores full raw JSON for future parsing improvements

## Service Classes

### TcgcsvClient
HTTP client with retry logic and exponential backoff.

```php
$client = new TcgcsvClient();
$groups = $client->getGroups();
$products = $client->getProducts($groupId);
$prices = $client->getPrices($groupId);
```

### TcgcsvImportService
Handles the import logic and database operations.

```php
$service = new TcgcsvImportService($client);

// Import specific entities
$groupStats = $service->importGroups();
$productStats = $service->importProductsByGroup($groupId);
$priceStats = $service->importPricesByGroup($groupId);

// Import everything
$stats = $service->importAll();

// Get run ID for logging/tracking
$runId = $service->getRunId();
```

## Example Output

```
╔════════════════════════════════════════════════════════╗
║  TCGCSV Pokemon Import (TCGplayer Category 3)         ║
╚════════════════════════════════════════════════════════╝

Run ID: tcgcsv_20251222_143052_aB3x9z
Category: Pokemon (ID: 3)
Target: All groups
Mode: all

Starting full import...

═══════════════════════════════════════════════════════
IMPORT SUMMARY
═══════════════════════════════════════════════════════

Groups:
+--------+-------+
| Metric | Value |
+--------+-------+
| Total  | 170   |
| New    | 165   |
| Updated| 5     |
| Failed | 0     |
+--------+-------+

Products:
+--------+-------+
| Metric | Value |
+--------+-------+
| Total  | 25000 |
| New    | 24800 |
| Updated| 200   |
| Failed | 0     |
+--------+-------+

Prices:
+--------+-------+
| Metric | Value |
+--------+-------+
| Total  | 75000 |
| New    | 75000 |
| Updated| 0     |
| Failed | 0     |
+--------+-------+

✓ Import completed in 125.43s
```

## Scheduled Imports

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Import TCGCSV prices daily at 3 AM
    $schedule->command('tcgcsv:import-pokemon --only=prices')
             ->dailyAt('03:00')
             ->emailOutputOnFailure('admin@example.com');
    
    // Full sync weekly
    $schedule->command('tcgcsv:import-pokemon')
             ->weekly()
             ->sundays()
             ->at('02:00');
}
```

## Querying Data

```php
use App\Models\TcgcsvGroup;
use App\Models\TcgcsvProduct;
use App\Models\TcgcsvPrice;

// Get all groups
$groups = TcgcsvGroup::all();

// Find product by TCGplayer ID
$product = TcgcsvProduct::where('product_id', 12345)->first();

// Get latest prices for a product
$prices = TcgcsvPrice::where('product_id', 12345)
    ->orderBy('snapshot_at', 'desc')
    ->get();

// Get all products in a group
$products = TcgcsvProduct::where('group_id', 123)->get();

// Search by card number
$card = TcgcsvProduct::where('card_number', '001')->first();
```

## Differences from pokemontcg.io Pipeline

| Feature | pokemontcg.io | tcgcsv.com |
|---------|---------------|------------|
| Tables | `pokemon_*` | `tcgcsv_*` |
| Models | `App\Models\Pokemon*` | `App\Models\Tcgcsv*` |
| Services | `App\Services\Pokemon*` | `App\Services\Tcgcsv\*` |
| Config | `config/pokemon.php` | `config/tcgcsv.php` |
| Command | `pokemon:*` | `tcgcsv:*` |
| Data | Card info, images | Pricing, market data |

## Troubleshooting

### Import Fails with Connection Error
- Check `TCGCSV_BASE_URL` in `.env`
- Increase `TCGCSV_TIMEOUT`
- Check tcgcsv.com status

### Rate Limiting (429 errors)
- Increase `TCGCSV_RETRY_SLEEP` in config
- Add delays between group imports
- Consider running during off-peak hours

### Missing card_number or rarity
- Check `extended_data` column in database
- API might not provide this data for all cards
- Raw JSON is always stored for manual extraction

## API Endpoints Used

```
GET https://tcgcsv.com/tcgplayer/3/groups
GET https://tcgcsv.com/tcgplayer/3/{groupId}/products  
GET https://tcgcsv.com/tcgplayer/3/{groupId}/prices
```

## Notes

- This pipeline is **completely independent** of the pokemontcg.io pipeline
- No shared tables, models, or services
- Can run both pipelines simultaneously
- TCGCSV focuses on pricing data, pokemontcg.io on card details
- Consider joining data in application layer if needed
