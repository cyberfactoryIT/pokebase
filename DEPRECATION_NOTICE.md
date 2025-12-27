# Deprecation Notice: Pokemon TCG API Tables

## Date: December 27, 2025

## Deprecated Tables
- `card_catalog` → renamed to `deprecated_card_catalog`
- `pokemon_sets` → renamed to `deprecated_pokemon_sets`

## Reasons for Deprecation

### 1. Data Completeness
- **card_catalog**: Only 263 cards imported
- **tcgcsv_products**: 30,757 cards (117x more complete)

### 2. API Stability
- Pokemon TCG API (`pokemontcg.io`): Frequent 504 timeouts
- TCGCSV API (`tcgcsv.com`): Stable and reliable

### 3. System Architecture
- **user_collection**: Already uses `tcgcsv_products.product_id`
- **deck_cards**: Already uses `tcgcsv_products.product_id`
- No foreign keys depend on deprecated tables

### 4. Image Quality (Not a Blocker)
- Pokemon TCG API: High-quality PNG images (734x1024px)
- TCGCSV: Lower quality JPG images (200px width)
- **Decision**: TCGCSV images are sufficient for current needs

## Deprecated Code References

### Commands (No Longer Needed)
- `app/Console/Commands/ImportAllPokemonCards.php`
- `app/Console/Commands/DownloadPokemonCards.php`
- `app/Console/Commands/ImportCardsFromFiles.php`
- `app/Console/Commands/ImportSetsFromFile.php`
- `app/Console/Commands/ListPokemonSets.php`
- `app/Console/Commands/CheckPokemonUpdates.php`
- `app/Console/Commands/SyncPokemonSets.php`

### Models (No Longer Needed)
- `app/Models/CardCatalog.php`
- `app/Models/PokemonSet.php`

### Services (No Longer Needed)
- `app/Services/PokemonImportService.php`

### Scripts (No Longer Needed)
- `download_pokemon_card.sh`

## Migration Path

### Current State (Production)
```sql
-- Active tables for collection system
tcgcsv_groups          -- 212 Pokemon groups
tcgcsv_products        -- 30,757 Pokemon cards
tcgcsv_prices          -- TCGPlayer prices
user_collection        -- Uses tcgcsv_products.product_id
deck_cards            -- Uses tcgcsv_products.product_id

-- Deprecated (renamed)
deprecated_card_catalog      -- 263 cards (Pokemon TCG API)
deprecated_pokemon_sets      -- 170 sets (Pokemon TCG API)
```

### Rollback Instructions
If you need to rollback the deprecation:
```bash
php artisan migrate:rollback --step=1
```

This will rename tables back:
- `deprecated_card_catalog` → `card_catalog`
- `deprecated_pokemon_sets` → `pokemon_sets`

### Complete Removal (Future)
After confirming no issues for 30+ days, you can:
1. Drop the deprecated tables
2. Remove the deprecated commands and models
3. Remove Pokemon TCG API config from `.env`

```bash
# Create new migration to drop tables permanently
php artisan make:migration drop_deprecated_pokemon_api_tables
```

## Active Systems

### Primary Card Database
- **TCGCSV** (`tcgcsv_products`, `tcgcsv_groups`)
  - 30,757 Pokemon cards
  - TCGPlayer prices included
  - Stable API
  - Multi-game support (Pokemon, MTG, Yu-Gi-Oh)

### European Pricing
- **Cardmarket** (`cardmarket_products`, `cardmarket_expansions`)
  - 62,629 products
  - 500 expansions
  - 134 expansions mapped to TCGCSV (26.8%)
  - 130 products mapped (5.72% - low due to granularity mismatch)

## Notes
- User collections are **NOT affected** - already using TCGCSV
- Decks are **NOT affected** - already using TCGCSV
- No data loss - tables renamed, not dropped
- Can rollback if needed
