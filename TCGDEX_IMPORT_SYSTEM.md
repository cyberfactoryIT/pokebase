# TCGdex Import System

Complete, isolated import pipeline for Pokemon TCG data from [TCGdex API](https://tcgdex.dev).

## Overview

This system imports Pokemon card sets and cards from TCGdex into dedicated database tables with the `tcgdx_` prefix. It is fully isolated from other import pipelines (TCGCSV, Cardmarket, RapidAPI, etc.) and can run independently.

## Database Schema

### Tables

- **tcgdx_import_runs**: Tracks all import runs with status, stats, and errors
- **tcgdx_sets**: Pokemon TCG sets with multilingual names and metadata
- **tcgdx_cards**: Individual cards with images, stats, and multilingual data

All tables use the `tcgdx_` prefix to avoid conflicts with existing tables.

## API Endpoints Used

From `https://api.tcgdex.net/v2`:

- `GET /en/sets` - List all Pokemon sets
- `GET /en/sets/{setId}` - Get set details with embedded cards list
- `GET /en/cards/{cardId}` - Get single card (if needed)

## Usage

### Import All Sets

```bash
php artisan tcgdx:import
```

This will:
1. Fetch all Pokemon sets from TCGdex
2. Import each set and its cards
3. Track progress in `tcgdx_import_runs`
4. Continue even if some sets fail (resilient import)

### Import Single Set

```bash
php artisan tcgdx:import --set=base1
```

Replace `base1` with any TCGdex set ID (e.g., `jungle`, `sv01`, `swsh1`).

### Fresh Import (Delete Existing Data)

```bash
php artisan tcgdx:import --fresh
```

⚠️ This will truncate all tcgdx tables and start from scratch.

## Features

### Idempotent & Resumable

- Sets and cards are **upserted** by their natural key (`tcgdex_id`)
- Running import multiple times won't create duplicates
- Failed sets are logged but don't stop the entire import
- Can resume from any point

### Multilingual Support

Names are stored as JSON objects:
```json
{
  "en": "Base Set",
  "fr": "Édition de base",
  "de": "Basis-Set"
}
```

Use `$set->getLocalizedName()` or `$card->getLocalizedName()` to get the appropriate translation.

### Raw Data Preservation

Full API responses are stored in `raw` JSON columns for:
- Forward compatibility
- Debugging
- Access to fields not mapped to columns

### Error Handling

- HTTP retries (3 attempts with backoff)
- Per-set transactions (one set failure doesn't rollback others)
- Import run marked as "success" if <20% of sets fail
- Failed sets logged in `stats.failed_sets`

## Models

```php
use App\Models\Tcgdx\TcgdxSet;
use App\Models\Tcgdx\TcgdxCard;
use App\Models\Tcgdx\TcgdxImportRun;

// Get all sets
$sets = TcgdxSet::orderBy('release_date', 'desc')->get();

// Get cards from a set
$set = TcgdxSet::where('tcgdex_id', 'base1')->first();
$cards = $set->cards;

// Get localized name
$name = $set->getLocalizedName('en'); // "Base Set"

// Last import run
$lastRun = TcgdxImportRun::latest()->first();
```

## Admin Interface

View import runs at:

```
/admin/tcgdx/import-runs
```

Shows:
- Import status (running/success/failed)
- Duration
- Sets/cards imported
- Error messages

## Configuration

Edit `config/tcgdx.php` or set environment variables:

```env
TCGDX_BASE_URL=https://api.tcgdex.net/v2
TCGDX_TIMEOUT=30
TCGDX_RETRY_COUNT=3
TCGDX_RETRY_SLEEP_MS=1000
```

## Testing

```bash
# Run all TCGdex tests
php artisan test --filter=Tcgdx

# Feature tests (with HTTP mocking)
php artisan test tests/Feature/TcgdxImportTest.php

# Unit tests (normalization)
php artisan test tests/Unit/TcgdxClientTest.php
```

## Architecture

```
app/
├── Console/Commands/
│   └── TcgdxImportCommand.php       # Artisan command
├── Http/Controllers/Admin/
│   └── TcgdxImportRunController.php # Admin UI
├── Models/Tcgdx/
│   ├── TcgdxSet.php                 # Set model
│   ├── TcgdxCard.php                # Card model
│   └── TcgdxImportRun.php           # Import run model
└── Services/Tcgdx/
    ├── TcgdxClient.php              # API client
    └── TcgdxImportService.php       # Import logic

config/
└── tcgdx.php                        # Configuration

database/migrations/
├── 2025_12_31_100001_create_tcgdx_import_runs_table.php
├── 2025_12_31_100002_create_tcgdx_sets_table.php
└── 2025_12_31_100003_create_tcgdx_cards_table.php

resources/views/admin/tcgdx/
└── import-runs.blade.php            # Admin UI

routes/
└── web.php                          # Admin route

tests/
├── Feature/TcgdxImportTest.php      # Integration tests
└── Unit/TcgdxClientTest.php         # Normalization tests
```

## Future Enhancements

- Price snapshot integration (TCGdex doesn't provide prices)
- Card search/filtering endpoints
- Set/card detail pages
- Sync with existing TCGCSV products via matching logic
- Incremental updates (only fetch changed sets)

## Notes

- TCGdex IDs are strings (e.g., "base1", "sv01") not integers
- Card numbers can contain letters (e.g., "1a", "SV01")
- Some sets have no official release date (stored as NULL)
- Energy cards have no HP/types/rarity in some cases
- Multilingual fields fallback to 'en' if locale not available

## Support

TCGdex API Documentation: https://tcgdex.dev/docs
