# Cardmarket ETL Pipeline Documentation

## Overview

The Cardmarket ETL (Extract, Transform, Load) pipeline is a robust, idempotent system for importing trading card product catalogues and price guides from Cardmarket (formerly MagicCardMarket / MKM). 

### Key Features

- **Idempotent**: Safe to run multiple times without data duplication
- **Historical Price Tracking**: Maintains daily price snapshots for trend analysis
- **Multi-Game Support**: Works with any TCG (Pokémon, MTG, Yu-Gi-Oh!, etc.)
- **Resumable**: Can restart failed imports without losing progress
- **Memory Efficient**: Streams large CSV files using generators
- **Queue Support**: Can run asynchronously via Laravel queues
- **Comprehensive Logging**: Dedicated log channel with detailed tracking

## Architecture

### Components

1. **Config** (`config/cardmarket.php`)
   - Download URLs, storage paths, import options
   - CSV parsing configuration
   - Column mapping for format flexibility
   - Queue and logging settings

2. **Database Tables**
   - `cardmarket_products`: Product catalogue (cards, expansions, games)
   - `cardmarket_price_quotes`: Historical price snapshots
   - `cardmarket_import_runs`: Audit trail and progress tracking

3. **Services**
   - `CardmarketDownloader`: Handles file downloads and ZIP extraction
   - `ProductCatalogueParser`: Streams and normalizes catalogue CSV
   - `PriceGuideParser`: Streams and normalizes price CSV
   - `CardmarketImporter`: Orchestrates batch imports with transactions

4. **Commands**
   - `cardmarket:download`: Download files from Cardmarket
   - `cardmarket:import`: Import data from CSV files
   - `cardmarket:etl`: Full pipeline orchestration

5. **Jobs** (Queue support)
   - `DownloadCardmarketFilesJob`
   - `ImportCardmarketCatalogueJob`
   - `ImportCardmarketPriceGuideJob`

## Installation

### 1. Configure Download URLs

Add to your `.env`:

```env
CARDMARKET_CATALOGUE_URL=https://example.com/catalogue.zip
CARDMARKET_PRICEGUIDE_URL=https://example.com/priceguide.zip
```

### 2. Run Migrations

```bash
php artisan migrate
```

This creates:
- `cardmarket_products` table
- `cardmarket_price_quotes` table
- `cardmarket_import_runs` table

### 3. Configure Queue (Optional)

If using queued imports:

```env
CARDMARKET_QUEUE_CONNECTION=database
CARDMARKET_QUEUE_NAME=cardmarket
```

### 4. Configure Logging (Optional)

```env
CARDMARKET_LOG_LEVEL=info
CARDMARKET_LOG_CHANNEL=cardmarket
```

Logs are written to `storage/logs/cardmarket.log` with 30-day rotation.

## Usage

### Quick Start: Full ETL Pipeline

```bash
# Download files, import catalogue, import prices (synchronous)
php artisan cardmarket:etl

# Run via queue (recommended for production)
php artisan cardmarket:etl --queue

# Specify custom snapshot date
php artisan cardmarket:etl --as-of=2025-12-27

# Force fresh download (ignore cache)
php artisan cardmarket:etl --force-download
```

### Step-by-Step: Individual Commands

#### 1. Download Files

```bash
# Download both catalogue and price guide
php artisan cardmarket:download

# Download only catalogue
php artisan cardmarket:download --catalogue

# Download only price guide
php artisan cardmarket:download --priceguide

# Force download even if cached
php artisan cardmarket:download --force
```

Files are downloaded to `storage/app/cardmarket/raw/` and extracted to `storage/app/cardmarket/extracted/`.

#### 2. Import Data

```bash
# Import both catalogue and prices
php artisan cardmarket:import

# Import only catalogue
php artisan cardmarket:import --catalogue

# Import only prices
php artisan cardmarket:import --priceguide

# Specify snapshot date for prices
php artisan cardmarket:import --as-of=2025-12-27

# Import from local file
php artisan cardmarket:import --from-local=/path/to/file.csv

# Queue the import
php artisan cardmarket:import --queue

# Dry run (parse without writing to DB)
php artisan cardmarket:import --dry-run
```

## Data Model

### Product Catalogue

**Table:** `cardmarket_products`

```
id                     : Primary key
cardmarket_product_id  : Unique product ID from Cardmarket
game                   : Game name (Pokemon, Magic, Yu-Gi-Oh!, etc.)
category               : Category (e.g., "Pokemon Single")
expansion              : Set/expansion name (e.g., "Base Set")
name                   : Card name (e.g., "Charizard")
rarity                 : Rarity (e.g., "Rare Holo")
language               : Language code (e.g., "en")
is_foil                : Boolean (foil/non-foil)
external_keys          : JSON (unmapped CSV columns)
raw                    : JSON (original CSV row)
created_at, updated_at
```

**Indexes:**
- `cardmarket_product_id` (unique)
- `game`, `expansion`, `name`, `language`, `is_foil`
- Composite: `(game, expansion)`, `(game, name)`

### Price Quotes

**Table:** `cardmarket_price_quotes`

```
id                     : Primary key
cardmarket_product_id  : Foreign key to products
as_of_date             : Snapshot date (date)
currency               : Currency code (e.g., "EUR")
low_price              : Lowest market price
avg_price              : Average sell price
trend_price            : Trend price (30-day average)
suggested_price        : Suggested retail price
foil_avg_price         : Average foil price (if separate)
updated_at_source      : Source update timestamp
raw                    : JSON (original CSV row)
created_at, updated_at
```

**Indexes:**
- Unique: `(cardmarket_product_id, as_of_date)`
- Foreign key: `cardmarket_product_id` → `cardmarket_products.cardmarket_product_id`

**Historical Tracking:**
Each import creates a new snapshot for the specified date. Old snapshots are never deleted, enabling price trend analysis.

### Import Runs

**Table:** `cardmarket_import_runs`

```
id                          : Primary key
run_uuid                    : UUID for this import run
type                        : Enum('catalogue','priceguide','full')
status                      : Enum('running','success','failed')
started_at, finished_at     : Timestamps
source_catalogue_version    : File hash/version
source_priceguide_version   : File hash/version
rows_read                   : Total rows parsed
rows_upserted               : Total rows written
error_message               : Failure details
meta                        : JSON (additional metadata)
created_at, updated_at
```

## Idempotency & Safety

### How It Works

1. **Catalogue Import**: Uses `upsert` with `cardmarket_product_id` as unique key
   - If product exists: updates fields
   - If product new: inserts
   - Result: No duplicates, always current data

2. **Price Import**: Uses `upsert` with `(cardmarket_product_id, as_of_date)` as unique key
   - If quote exists for that date: updates prices
   - If quote new: inserts
   - Result: One quote per product per day, full history

3. **Safe to Re-run**: Can run imports multiple times without corruption
   - Failed imports can be restarted
   - Overlapping schedules handled by `withoutOverlapping()`

## Scheduling

The ETL pipeline runs automatically via Laravel's scheduler.

**File:** `routes/console.php`

```php
Schedule::command('cardmarket:etl --queue')
    ->dailyAt('02:10')
    ->timezone('Europe/Copenhagen')
    ->withoutOverlapping()
    ->onOneServer();
```

**Setup:**

Add to your cron:
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

**Customization:**

```php
// Different time
->dailyAt('03:00')

// Different frequency
->weekly()
->twiceDaily(1, 13)

// Only on specific days
->weekdays()
->mondays()
```

## Performance & Optimization

### Memory Management

- **Streaming Parsers**: Use PHP generators (`yield`) to read CSV row-by-row
- **Batch Processing**: Processes rows in chunks (default: 2000)
- **Lazy Loading**: No full-file loading into memory

### Database Optimization

- **Transactions**: Each chunk wrapped in transaction for atomicity
- **Batch Upserts**: Uses Laravel's `upsert()` for efficient bulk operations
- **Indexes**: All foreign keys and frequently queried columns indexed

### Configuration

```env
# Chunk size (rows per batch)
CARDMARKET_CHUNK_SIZE=2000

# Progress logging interval (every N chunks)
CARDMARKET_PROGRESS_INTERVAL=10

# Download cache duration (hours)
CARDMARKET_CACHE_HOURS=24
```

## Monitoring & Troubleshooting

### Check Import Status

```bash
# View scheduled tasks
php artisan schedule:list

# View queue jobs
php artisan queue:work --queue=cardmarket --once

# Check logs
tail -f storage/logs/cardmarket.log
```

### Query Import History

```php
use App\Models\CardmarketImportRun;

// Recent runs
$runs = CardmarketImportRun::recent(10)->get();

// Failed runs
$failed = CardmarketImportRun::failed()->get();

// Successful runs with stats
$stats = CardmarketImportRun::successful()
    ->select('type', DB::raw('SUM(rows_upserted) as total'))
    ->groupBy('type')
    ->get();
```

### Common Issues

**1. Download Fails**

```
Error: Catalogue URL not configured
```

**Solution:** Set `CARDMARKET_CATALOGUE_URL` in `.env`

**2. Import Fails**

```
Error: No CSV file found in ZIP archive
```

**Solution:** Verify ZIP contains valid CSV. Check `storage/app/cardmarket/extracted/` for extracted files.

**3. Memory Exhaustion**

```
Fatal error: Allowed memory size exhausted
```

**Solution:** Reduce chunk size:
```env
CARDMARKET_CHUNK_SIZE=1000
```

**4. Foreign Key Constraint**

```
Error: Cannot add price quote, product doesn't exist
```

**Solution:** Import catalogue before price guide:
```bash
php artisan cardmarket:import --catalogue
php artisan cardmarket:import --priceguide
```

## Testing

### Run Tests

```bash
# All tests
php artisan test

# Cardmarket tests only
php artisan test --filter=Cardmarket

# Specific test
php artisan test --filter=test_can_import_catalogue_from_fixture
```

### Test Fixtures

Located in `tests/Fixtures/cardmarket/`:
- `catalogue_test.csv`: Sample product data (8 products)
- `priceguide_test.csv`: Sample price data (8 quotes)

### Test Coverage

- Catalogue import from CSV
- Price guide import from CSV
- Idempotency (multiple imports)
- Historical snapshots (multiple dates)
- Dry run mode
- Unique constraints

## API Integration (Future)

The current implementation uses downloadable CSV files. For real-time API integration:

1. Implement `CardmarketApiClient` service
2. Add API credentials to config
3. Create separate commands for API sync
4. Keep CSV import as fallback option

## Advanced Usage

### Custom Column Mapping

If Cardmarket changes CSV format, update `config/cardmarket.php`:

```php
'mapping' => [
    'catalogue' => [
        'product_id' => ['idProduct', 'ProductID', 'id'],
        'name' => ['Name', 'CardName', 'card_name'],
        // Add new aliases as needed
    ],
],
```

### Filtering Imports

Modify parsers to filter rows:

```php
// In ProductCatalogueParser::normalizeRow()
if ($data['game'] !== 'Pokemon') {
    return null; // Skip non-Pokemon cards
}
```

### Custom Price Calculations

Extend `CardmarketPriceQuote` model:

```php
public function getMarketValueAttribute()
{
    // Custom logic: prefer trend over average
    return $this->trend_price ?? $this->avg_price ?? $this->low_price;
}
```

### Integration with Existing System

```php
// Example: Link to your existing cards table
class CardmarketProduct extends Model
{
    public function localCard()
    {
        return $this->hasOne(Card::class, 'external_id', 'cardmarket_product_id');
    }
}
```

## Configuration Reference

### Environment Variables

```env
# Download URLs
CARDMARKET_CATALOGUE_URL=
CARDMARKET_PRICEGUIDE_URL=

# Import Options
CARDMARKET_CHUNK_SIZE=2000
CARDMARKET_PROGRESS_INTERVAL=10
CARDMARKET_TIMEZONE=Europe/Copenhagen
CARDMARKET_CURRENCY=EUR
CARDMARKET_CACHE_HOURS=24

# CSV Parsing
CARDMARKET_CSV_DELIMITER=,
CARDMARKET_CSV_ENCLOSURE="
CARDMARKET_CSV_ESCAPE=\\

# Queue
CARDMARKET_QUEUE_CONNECTION=database
CARDMARKET_QUEUE_NAME=cardmarket
CARDMARKET_QUEUE_TIMEOUT=3600
CARDMARKET_QUEUE_TRIES=3

# Logging
CARDMARKET_LOG_CHANNEL=cardmarket
CARDMARKET_LOG_LEVEL=info
```

## Support & Maintenance

### Logs Location
- Application logs: `storage/logs/cardmarket.log` (30-day rotation)
- Laravel logs: `storage/logs/laravel.log`

### Database Maintenance

```bash
# Clean old import runs (keep last 90 days)
DB::table('cardmarket_import_runs')
    ->where('created_at', '<', now()->subDays(90))
    ->delete();

# Archive old price quotes
# (Consider moving to separate archive table)
```

### Backup Recommendations

Before major operations:
```bash
# Backup database
php artisan db:backup

# Backup downloaded files
tar -czf cardmarket_data_$(date +%Y%m%d).tar.gz storage/app/cardmarket/
```

## License & Credits

Part of the Pokebase multi-game collection management system.

Built with Laravel 11, using industry best practices for ETL pipelines.

---

**Last Updated:** December 27, 2025  
**Version:** 1.0.0  
**Maintainer:** Pokebase Development Team
