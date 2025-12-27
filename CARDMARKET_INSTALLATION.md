# 🚀 Cardmarket ETL Pipeline - Installation & Setup Guide

## Overview

This guide will walk you through setting up and running the Cardmarket ETL pipeline for the first time.

## Prerequisites

- ✅ Laravel 11+ project
- ✅ PHP 8.1+
- ✅ MySQL/PostgreSQL database
- ✅ Composer installed
- ✅ Access to Cardmarket data files (CSV or ZIP format)

## Installation Steps

### Step 1: Run Migrations

Execute the database migrations to create the required tables:

```bash
php artisan migrate
```

This creates 3 tables:
- `cardmarket_products` - Product catalogue
- `cardmarket_price_quotes` - Historical price snapshots
- `cardmarket_import_runs` - Import audit trail

**Verify migrations:**
```bash
php artisan migrate:status
```

### Step 2: Configure Environment

Add the following to your `.env` file:

```env
# Cardmarket Download URLs (required if using download command)
CARDMARKET_CATALOGUE_URL=https://example.com/path/to/catalogue.zip
CARDMARKET_PRICEGUIDE_URL=https://example.com/path/to/priceguide.zip

# Import Configuration (optional - has sensible defaults)
CARDMARKET_CHUNK_SIZE=2000
CARDMARKET_PROGRESS_INTERVAL=10
CARDMARKET_TIMEZONE=Europe/Copenhagen
CARDMARKET_CURRENCY=EUR
CARDMARKET_CACHE_HOURS=24

# Queue Configuration (optional - for async processing)
CARDMARKET_QUEUE_CONNECTION=database
CARDMARKET_QUEUE_NAME=cardmarket
CARDMARKET_QUEUE_TIMEOUT=3600
CARDMARKET_QUEUE_TRIES=3

# Logging Configuration (optional)
CARDMARKET_LOG_CHANNEL=cardmarket
CARDMARKET_LOG_LEVEL=info
```

### Step 3: Create Storage Directories

The system will create these automatically, but you can pre-create them:

```bash
mkdir -p storage/app/cardmarket/{raw,extracted,archive}
chmod -R 775 storage/app/cardmarket
```

### Step 4: Verify Commands

List available Cardmarket commands:

```bash
php artisan list cardmarket
```

You should see:
- `cardmarket:download`
- `cardmarket:import`
- `cardmarket:etl`

### Step 5: Run Test Import (Optional but Recommended)

Test with the included fixture files:

```bash
# Import test catalogue
php artisan cardmarket:import \
    --catalogue \
    --from-local=tests/Fixtures/cardmarket/catalogue_test.csv

# Import test prices
php artisan cardmarket:import \
    --priceguide \
    --from-local=tests/Fixtures/cardmarket/priceguide_test.csv \
    --as-of=2025-12-27
```

**Verify test data:**
```bash
php artisan tinker
>>> App\Models\CardmarketProduct::count()
=> 8
>>> App\Models\CardmarketPriceQuote::count()
=> 8
```

### Step 6: Run Full ETL Pipeline

#### Option A: Synchronous (for testing)

```bash
php artisan cardmarket:etl
```

This will:
1. Download catalogue and price guide files
2. Extract ZIP archives
3. Import products
4. Import prices
5. Log all operations

#### Option B: Queued (for production)

```bash
# Start queue worker in background
php artisan queue:work --queue=cardmarket --daemon &

# Run ETL via queue
php artisan cardmarket:etl --queue
```

### Step 7: Verify Import

Check the import status:

```bash
# View recent import runs
php artisan tinker
>>> App\Models\CardmarketImportRun::recent(5)->get(['run_uuid', 'type', 'status', 'rows_upserted'])

# Check products imported
>>> App\Models\CardmarketProduct::count()

# Check price quotes
>>> App\Models\CardmarketPriceQuote::count()
```

Check logs:
```bash
tail -f storage/logs/cardmarket.log
```

### Step 8: Setup Scheduling (Production)

The pipeline is configured to run daily at 2:10 AM (Europe/Copenhagen).

**Add Laravel scheduler to cron:**

```bash
crontab -e
```

Add this line:
```
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

**Verify schedule:**
```bash
php artisan schedule:list
```

You should see:
```
10 2 * * *  php artisan cardmarket:etl --queue .... Next Due: X hours from now
```

## Usage Examples

### Download Only

```bash
# Download both files
php artisan cardmarket:download

# Download only catalogue
php artisan cardmarket:download --catalogue

# Force fresh download (ignore cache)
php artisan cardmarket:download --force
```

### Import Only

```bash
# Import from latest downloaded files
php artisan cardmarket:import

# Import only catalogue
php artisan cardmarket:import --catalogue

# Import with specific date
php artisan cardmarket:import --as-of=2025-12-27

# Import from local file
php artisan cardmarket:import --from-local=/path/to/file.csv

# Dry run (no database writes)
php artisan cardmarket:import --dry-run

# Queue the import
php artisan cardmarket:import --queue
```

### Full ETL

```bash
# Run complete pipeline
php artisan cardmarket:etl

# Queue all operations
php artisan cardmarket:etl --queue

# Custom snapshot date
php artisan cardmarket:etl --as-of=2025-12-27

# Force download + import
php artisan cardmarket:etl --force-download
```

## Monitoring & Troubleshooting

### Check Logs

```bash
# Real-time log monitoring
tail -f storage/logs/cardmarket.log

# Check for errors
grep ERROR storage/logs/cardmarket.log

# View Laravel logs
tail -f storage/logs/laravel.log
```

### Query Import Status

```php
use App\Models\CardmarketImportRun;

// Recent runs
$runs = CardmarketImportRun::recent(10)->get();

// Failed runs
$failed = CardmarketImportRun::failed()->get();
foreach ($failed as $run) {
    echo $run->error_message . "\n";
}

// Successful runs stats
$stats = CardmarketImportRun::successful()
    ->selectRaw('type, COUNT(*) as count, SUM(rows_upserted) as total')
    ->groupBy('type')
    ->get();
```

### Common Issues

#### 1. "Download URL not configured"
```
Error: Catalogue URL not configured. Set CARDMARKET_CATALOGUE_URL in .env
```
**Solution:** Add URLs to `.env` file

#### 2. "No CSV file found in ZIP"
```
Error: No CSV file found in ZIP archive
```
**Solution:** Verify ZIP contains CSV. Manually extract and check contents.

#### 3. "Foreign key constraint violation"
```
Error: Cannot add price quote, product doesn't exist
```
**Solution:** Import catalogue before price guide:
```bash
php artisan cardmarket:import --catalogue
php artisan cardmarket:import --priceguide
```

#### 4. "Memory exhausted"
```
Fatal error: Allowed memory size exhausted
```
**Solution:** Reduce chunk size:
```env
CARDMARKET_CHUNK_SIZE=1000
```

#### 5. Queue jobs stuck
```bash
# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear queue
php artisan queue:flush
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

# With coverage
php artisan test --coverage
```

### Manual Testing Checklist

- [ ] Migrations run successfully
- [ ] Commands are registered (`php artisan list cardmarket`)
- [ ] Test fixtures import correctly
- [ ] Products are created (no duplicates)
- [ ] Prices are created (multiple dates work)
- [ ] Import runs are logged
- [ ] Logs are written to `cardmarket.log`
- [ ] Schedule is registered (`php artisan schedule:list`)
- [ ] Queue jobs dispatch successfully

## Performance Tuning

### For Large Imports (>1M rows)

```env
# Reduce chunk size to avoid memory issues
CARDMARKET_CHUNK_SIZE=1000

# Increase PHP memory limit (php.ini or .env)
memory_limit=512M

# Increase queue timeout
CARDMARKET_QUEUE_TIMEOUT=7200

# Disable query logging (database.php)
'connections' => [
    'mysql' => [
        'options' => [
            PDO::ATTR_EMULATE_PREPARES => true
        ]
    ]
]
```

### Queue Workers

```bash
# Run multiple workers for parallel processing
php artisan queue:work --queue=cardmarket --sleep=3 --tries=3 &
php artisan queue:work --queue=cardmarket --sleep=3 --tries=3 &
php artisan queue:work --queue=cardmarket --sleep=3 --tries=3 &

# Supervisor configuration (recommended for production)
# See Laravel docs: https://laravel.com/docs/queues#supervisor-configuration
```

## Maintenance

### Cleanup Old Import Runs

```php
// Keep last 90 days
use App\Models\CardmarketImportRun;

CardmarketImportRun::where('created_at', '<', now()->subDays(90))
    ->delete();
```

### Archive Old Price Quotes

Consider moving old price data to a separate archive table:

```php
// Example: Archive prices older than 1 year
use App\Models\CardmarketPriceQuote;

$oldQuotes = CardmarketPriceQuote::where('as_of_date', '<', now()->subYear())
    ->get();

// Move to archive table (implement as needed)
```

### Backup Recommendations

```bash
# Database backup before major imports
php artisan backup:run # (if using spatie/laravel-backup)

# Or manual backup
mysqldump -u user -p database > backup_$(date +%Y%m%d).sql

# Backup downloaded files
tar -czf cardmarket_data_$(date +%Y%m%d).tar.gz storage/app/cardmarket/
```

## Integration with Existing System

### Link to Your Cards Table

```php
// In your existing Card model
public function cardmarketProduct()
{
    return $this->belongsTo(
        \App\Models\CardmarketProduct::class,
        'cardmarket_product_id',
        'cardmarket_product_id'
    );
}

// In CardmarketProduct model
public function localCard()
{
    return $this->hasOne(
        \App\Models\Card::class,
        'cardmarket_product_id',
        'cardmarket_product_id'
    );
}
```

### Use Price Data

```php
use App\Models\CardmarketProduct;

// Get latest price for a product
$product = CardmarketProduct::with('latestPriceQuote')->find($id);
$latestPrice = $product->latestPriceQuote?->avg_price;

// Get price history
$history = $product->priceQuotes()
    ->orderBy('as_of_date', 'desc')
    ->limit(30)
    ->get();

// Get products with prices
$products = CardmarketProduct::whereHas('priceQuotes')->get();
```

## Production Checklist

Before deploying to production:

- [ ] Environment variables configured in `.env`
- [ ] Migrations tested and documented
- [ ] Download URLs verified and accessible
- [ ] Queue system configured and tested
- [ ] Cron job added for scheduler
- [ ] Queue workers running (via Supervisor)
- [ ] Logs directory writable
- [ ] Storage directories created with correct permissions
- [ ] Error monitoring configured (Sentry, Bugsnag, etc.)
- [ ] Backup strategy in place
- [ ] Team trained on monitoring and troubleshooting

## Support

For issues or questions:

1. Check logs: `storage/logs/cardmarket.log`
2. Review documentation: `docs/cardmarket-etl.md`
3. Check import runs: Query `cardmarket_import_runs` table
4. Run tests to verify integrity

## Next Steps

1. ✅ Complete installation steps above
2. ✅ Run test import with fixtures
3. ✅ Configure real Cardmarket URLs
4. ✅ Run full ETL pipeline
5. ✅ Setup scheduling
6. ✅ Monitor first scheduled run
7. ✅ Integrate with your existing card system

---

**Congratulations!** 🎉

Your Cardmarket ETL pipeline is now ready to import trading card data!

For detailed documentation, see: `docs/cardmarket-etl.md`
