# 📊 Pokebase - Project Status
*Last Updated: 27 December 2025*

---

## 🔄 Major Migration Complete - December 27, 2025

**Pokemon TCG API → TCGCSV Migration**
- ✅ Removed Pokemon TCG API system (card_catalog, pokemon_sets - only 263 cards)
- ✅ Migrated to TCGCSV as primary database (141,675 products across all games)
- ✅ Cardmarket integration complete (62,629 products, 500 expansions)
- ✅ Cleaned up 14 deprecated files (commands, models, services, scripts)
- ✅ Zero data loss - user collections already on TCGCSV

---

## 🎮 Multi-Game System - ✅ IMPLEMENTED

### Supported Games
| Game | Code | TCGCSV Category | Status | Import Status |
|------|------|-----------------|--------|---------------|
| Pokémon TCG | `pokemon` | 3 | ✅ Active | ✅ Ready |
| Magic: The Gathering | `mtg` | 1 | ✅ Active | ✅ Ready |
| Yu-Gi-Oh! | `yugioh` | 2 | ✅ Active | 🔄 Ready |

### Core Features

#### ✅ Database Architecture
- [x] `games` table with game definitions
- [x] `game_user` pivot table for user-game associations
- [x] `game_id` column in `tcgcsv_groups`
- [x] `game_id` column in `tcgcsv_products`
- [x] `game_id` column in `tcgcsv_prices`
- [x] Composite unique indexes: `(group_id, category_id)` on groups
- [x] Foreign key relationships maintained
- [x] GamesSeeder for automatic population
- [x] `articles` table for informational content per game

#### ✅ Informational Articles System - NEW! 🆕
- [x] Articles table with game_id, category, markdown content
- [x] Admin CRUD interface (SuperAdmin only)
- [x] Category filtering on dashboard
- [x] HTML5 native accordion (details/summary)
- [x] Markdown to HTML conversion (XSS-safe)
- [x] Image upload support (max 1MB, decorative)
- [x] External URL linking
- [x] Sort order and publish scheduling
- [x] 12 seed articles (4 per game)
- [x] Responsive 3-column grid layout

#### ✅ Automatic Game Scoping
- [x] `SetCurrentGame` middleware - validates and sets game context
- [x] Session-based game storage (`current_game_id`)
- [x] All queries automatically filtered by current game
- [x] `CurrentGameContext` helper service
- [x] Global helper functions: `currentGame()`, `currentGameId()`, `availableGames()`
- [x] Blade directives: `@currentGame`, `@availableGames`

#### ✅ User Interface
- [x] Game selector dropdown in navigation (top-right)
- [x] Profile page with game management section
- [x] Dynamic game name and logo on dashboard
- [x] Conditional logo display (only if file exists)
- [x] All data views scoped to current game
- [x] User can enable/disable games in profile

---

## 💶 Cardmarket Integration - ✅ IMPLEMENTED

### European Pricing System
| Component | Status | Records | Notes |
|-----------|--------|---------|-------|
| Products Import | ✅ Complete | 62,629 | JSON API (singles only) |
| Price Quotes Import | ✅ Complete | 62,629 | Daily snapshots |
| Expansions Mapping | ✅ Complete | 500 | HTML-scraped expansion list |
| Auto-Matching | ✅ Complete | 134/500 | 26.8% expansions matched to TCGCSV |
| Product Matching | ⚠️ Partial | 130/2,272 | 5.72% (granularity mismatch) |

### Key Features
- [x] Daily JSON download from Cardmarket S3 CDN
- [x] Products: name, expansion, rarity, number, game_set_name
- [x] Prices: SELL avg, LOW, TREND, TRENDPRICE tracking
- [x] Import tracking via `cardmarket_import_runs`
- [x] Chunk processing (500 records/batch) with memory optimization
- [x] Expansion auto-matching with fuzzy algorithms
- [x] Product auto-matching (limited by data granularity)

### Database Tables
```
cardmarket_products (62,629 records)
  - cardmarket_product_id, id_expansion, name, game_set_name
  - category_id, rarity, exp_rarity, number
  - tcgcsv_product_id (nullable, FK)

cardmarket_price_quotes (62,629+ records, growing daily)
  - cardmarket_product_id (FK), as_of_date
  - avg_sell_price, low_price, trend_price, trend_price_7d, trend_price_30d

cardmarket_expansions (500 records)
  - cardmarket_expansion_id, name
  - tcgcsv_group_id (nullable, FK to tcgcsv_groups)

cardmarket_import_runs (tracking table)
  - import_type, file_path, records_processed, status
```

### Auto-Matching Results
**Expansion Level (134 matched out of 500 = 26.8%)**
- High confidence (≥90%): 42 expansions
- Medium confidence (80-89%): 92 expansions
- 366 unmapped: Japanese/European exclusive sets

**Product Level (130 matched out of 2,272 = 5.72%)**
- High confidence (≥95%): 130 products
- Low success rate due to data structure differences:
  - TCGCSV: Aggregated variants (1 product = "Pikachu")
  - Cardmarket: Separate variants (5-10 products = "Pikachu Reverse", "Pikachu 1st Ed", etc.)

### Commands
```bash
# Download and import Cardmarket data
php artisan cardmarket:download    # Downloads JSON from S3
php artisan cardmarket:import      # Imports products + prices

# Auto-matching
php artisan cardmarket:match-expansions --threshold=80 --auto-confirm
php artisan cardmarket:match-products --threshold=85 --auto-confirm
```

---

#### ✅ Data Import System
- [x] Generic `tcgcsv:import` command
- [x] Support for `--game={code}` flag
- [x] Support for `--only={groups|products|prices|all}` flag
- [x] Support for `--groupId={id}` for specific imports
- [x] Automatic game_id assignment during import
- [x] TcgcsvClient accepts dynamic category_id
- [x] TcgcsvImportService accepts dynamic game_id
- [x] Models updated with `game_id` in fillable

#### ✅ Controllers Updated
All controllers now filter by current game:
- [x] `DashboardController` - stats per game
- [x] `TcgExpansionController` - expansions per game
- [x] `CollectionController` - collections per game
- [x] `CurrentGameController` - handles game switching

---

## 🗂️ Database Structure

### Active Tables

#### Core Game System
```
games (id, name, code, slug, tcgcsv_category_id, is_active, timestamps)
game_user (user_id, game_id, is_enabled, timestamps)
```

#### TCGCSV (Primary Card Database)
```
tcgcsv_groups (652 groups) - with game_id
tcgcsv_products (141,675 products) - with game_id
tcgcsv_prices (pricing data) - with game_id
```

#### Cardmarket (European Pricing)
```
cardmarket_products (62,629 products)
cardmarket_price_quotes (62,629+ snapshots)
cardmarket_expansions (500 expansions)
cardmarket_import_runs (tracking)
```

#### User Data
```
user_collection - uses tcgcsv_products.product_id
deck_cards - uses tcgcsv_products.product_id
```

### Deprecated/Removed Tables
```
❌ card_catalog (Pokemon TCG API) - DROPPED
❌ pokemon_sets (Pokemon TCG API) - DROPPED
❌ pokemon_import_logs - DROPPED
```

### Migrations Applied

#### Multi-Game System
- `2025_12_26_000000_add_tcgcsv_category_id_to_games_table.php`
- `2025_12_26_010000_create_game_user_table.php`
- `2025_12_26_015000_add_slug_to_games_table.php`
- `2025_12_26_020000_add_game_id_to_tcgcsv_tables.php`
- `2025_12_26_190409_create_articles_table.php`

#### Cardmarket System
- `2025_12_27_120000_create_cardmarket_tables.php`
- `2025_12_27_134556_create_cardmarket_expansions_table.php`
- `2025_12_27_143840_add_tcgcsv_product_id_to_cardmarket_products_table.php`

#### Pokemon TCG API Deprecation
- `2025_12_27_150000_deprecate_pokemon_api_tables.php` (renamed tables)
- `2025_12_27_151000_drop_pokemon_api_tables.php` (dropped permanently)

---

## 🔧 Technical Implementation

### Helper Functions
**File:** `app/helpers.php`
```php
currentGame() // Returns current Game model
currentGameId() // Returns current game ID
availableGames() // Returns user's enabled games
```

### Middleware
**File:** `app/Http/Middleware/SetCurrentGame.php`
- Registered in `web` middleware group
- Runs on every request
- Sets game from session or user's first game
- Shares `$currentGame` and `$availableGames` with all views

### Service Classes
**File:** `app/Services/CurrentGameContext.php`
- Manages game context logic
- Handles game switching
- Validates user access to games

### Routes
```php
POST /current-game → CurrentGameController@store (switch game)
```

### Commands
```bash
php artisan tcgcsv:import --game=pokemon --only=all
php artisan tcgcsv:import --game=mtg --only=groups
php artisan tcgcsv:import --game=yugioh --groupId=5 --only=products
php artisan db:seed --class=GamesSeeder
php artisan db:seed --class=ArticlesSeeder
```

### Admin Routes (SuperAdmin Only)
```php
GET  /superadmin/articles         → admin.articles.index (list)
GET  /superadmin/articles/create  → admin.articles.create
POST /superadmin/articles         → admin.articles.store
GET  /superadmin/articles/{id}/edit → admin.articles.edit
PUT  /superadmin/articles/{id}    → admin.articles.update
DELETE /superadmin/articles/{id}  → admin.articles.destroy
```

---

## 📁 Files Modified/Created

### New Files (Multi-Game System)
```
app/Http/Controllers/CurrentGameController.php
app/Http/Middleware/SetCurrentGame.php
app/Http/Middleware/EnsureSuperAdmin.php
app/Services/CurrentGameContext.php
app/Console/Commands/TcgcsvImport.php
app/Models/Article.php
app/Http/Controllers/Admin/ArticleController.php
database/seeders/GamesSeeder.php
database/seeders/ArticlesSeeder.php
resources/views/profile/game-management.blade.php
resources/views/admin/articles/*.blade.php
deploy.sh
DEPLOYMENT.md
STATUS.md (this file)
PROJECT_SNAPSHOT.md
```

### New Files (Cardmarket System)
```
app/Models/CardmarketProduct.php
app/Models/CardmarketPriceQuote.php
app/Models/CardmarketExpansion.php
app/Models/CardmarketImportRun.php
app/Console/Commands/CardmarketDownload.php
app/Console/Commands/CardmarketImport.php
app/Console/Commands/CardmarketMatchExpansions.php
app/Console/Commands/CardmarketMatchProducts.php
app/Services/Cardmarket/CardmarketClient.php
app/Services/Cardmarket/CardmarketImportService.php
app/Services/Cardmarket/Parsers/ProductCatalogueParser.php
app/Services/Cardmarket/Parsers/PriceGuideParser.php
config/cardmarket.php
database/seeders/CardmarketExpansionsSeeder.php
database/migrations/2025_12_27_*.php (Cardmarket tables)
DEPRECATION_NOTICE.md
```

### Removed Files (Pokemon TCG API Cleanup)
```
❌ app/Console/Commands/ImportAllPokemonCards.php
❌ app/Console/Commands/DownloadPokemonCards.php
❌ app/Console/Commands/ImportCardsFromFiles.php
❌ app/Console/Commands/ImportSetsFromFile.php
❌ app/Console/Commands/ListPokemonSets.php
❌ app/Console/Commands/CheckPokemonUpdates.php
❌ app/Console/Commands/SyncPokemonSets.php
❌ app/Console/Commands/PokemonImportStatus.php
❌ app/Models/CardCatalog.php
❌ app/Models/PokemonSet.php
❌ app/Models/PokemonImportLog.php
❌ app/Services/PokemonImportService.php
❌ config/pokemon.php
❌ download_pokemon_card.sh
❌ download_pokemon_card_test.sh
```

### Modified Files
```
app/Models/TcgcsvGroup.php (+ game_id in fillable)
app/Models/TcgcsvProduct.php (+ game_id in fillable)
app/Models/TcgcsvPrice.php (+ game_id in fillable)
app/Models/Game.php (removed cardCatalog(), kept tcgcsvProducts/Groups)
app/Models/User.php (games() relationship)
app/Models/UserCollection.php (uses tcgcsv_products.product_id)
app/Http/Controllers/DashboardController.php (game scoping + articles + category filter)
app/Http/Controllers/TcgExpansionController.php (game scoping)
app/Http/Controllers/CollectionController.php (game scoping)
app/Services/Tcgcsv/TcgcsvClient.php (dynamic category_id)
app/Services/Tcgcsv/TcgcsvImportService.php (dynamic game_id)
bootstrap/app.php (middleware registration)
database/seeders/DatabaseSeeder.php (+ GamesSeeder + ArticlesSeeder)
resources/views/layouts/navigation.blade.php (game dropdown)
resources/views/dashboard.blade.php (dynamic game name/logo + articles section + category filter)
resources/views/profile/edit.blade.php (game management section)
routes/web.php (+ current-game route + admin articles routes)
composer.json (helpers autoload)
.env (removed POKEMON_API_* variables)
```

---

## 🚀 Deployment Status

### Local Environment
- ✅ All migrations applied (including Cardmarket + Pokemon API deprecation)
- ✅ Games seeded (3 games: Pokemon, MTG, Yu-Gi-Oh)
- ✅ TCGCSV fully imported:
  - 652 groups across all games
  - 141,675 products (Pokemon: 30,757, MTG: ~100k, YGO: ~10k)
  - Prices updated
- ✅ Cardmarket fully imported:
  - 62,629 products (Pokemon singles)
  - 62,629+ price quotes
  - 500 expansions seeded
  - 134 expansions auto-matched (26.8%)
  - 130 products auto-matched (5.72%)
- ✅ Pokemon TCG API fully removed (0 residual tables/files)

### Production Deployment
- 📦 Ready for deployment
- ✅ `deploy.sh` script created
- ✅ `DEPLOYMENT.md` guide created
- ✅ `DEPRECATION_NOTICE.md` for reference
- ✅ Zero breaking changes (user_collection already on TCGCSV)
- ⚠️ Requires manual execution on server

---

## 📝 Next Steps / TODO

### High Priority
- [ ] **UI for Cardmarket prices**: Display European pricing alongside TCGPlayer
  - Option 1: Separate price columns (TCGPlayer | Cardmarket)
  - Option 2: User preference for default price source
  - Option 3: Show both with currency toggle (USD/EUR)
- [ ] **Improve Cardmarket product matching**:
  - Consider fuzzy grouping (aggregate variants)
  - Manual mapping UI for popular cards
  - Or accept expansion-level matching only (26.8% coverage)
- [ ] Add MTG and Yu-Gi-Oh logos to `/public/images/logos/`
- [ ] Test multi-game switching thoroughly

### Production Deployment
- [ ] Backup production database
- [ ] Run `./deploy.sh` on server
- [ ] Run Cardmarket import on production
- [ ] Associate existing users with Pokemon game
- [ ] Test multi-game functionality on production
- [ ] Monitor Cardmarket daily price updates

### Multi-Language Cards System
- [x] Add `language` column to `tcgcsv_products` (default: 'en')
- [x] Add `language` column to `user_collection` (default: 'en')
- [x] Update unique constraint to include language
- [ ] Find data source for non-English cards (Pokemon/MTG/YuGiOh)
- [ ] UI: Language filter in collection view
- [ ] UI: Language selector when adding card to collection
- [ ] Import cards in multiple languages (pending data source)

### Future Enhancements
- [ ] Scheduled Cardmarket daily updates (cron job)
- [ ] Price history charts (TCGCSV + Cardmarket trends)
- [ ] Admin panel for manual Cardmarket matching
- [ ] Cross-game collection comparison
- [ ] Game statistics and analytics
- [ ] Support for additional TCG games
- [ ] Consider premium Pokemon TCG API integration (if available)

---

## 🧪 Testing Status

### Verified Functionality
- ✅ Game switching via dropdown
- ✅ Session persistence of selected game
- ✅ Dashboard shows correct game data
- ✅ Collections filtered by game
- ✅ Expansions filtered by game
- ✅ Import command works for multiple games
- ✅ game_id correctly assigned during import
- ✅ Users can manage games in profile
- ✅ Cardmarket full import (62,629 products + prices)
- ✅ Cardmarket expansion matching (134/500 = 26.8%)
- ✅ Cardmarket product matching (130/2,272 = 5.72%)
- ✅ Pokemon TCG API fully removed (0 residual tables)
- ✅ user_collection uses TCGCSV (no migration needed)

### Known Issues
- ⚠️ Cardmarket product matching low (5.72%) due to granularity mismatch
  - Cardmarket: per-variant products (reverse, 1st ed, etc.)
  - TCGCSV: aggregated products
  - Solution: Use expansion-level matching or manual mapping UI
- ✅ Import `--only=all` display bug (fixed)
- ✅ game_id fillable arrays (fixed)
- ✅ Logo path hardcoded (fixed)

---

## 📚 Documentation

### Available Docs
- `MULTI_GAME_SYSTEM.md` - Architecture overview
- `MULTI_GAME_IMPLEMENTATION.md` - Implementation details
- `MULTI_GAME_TESTING.md` - Testing guide
- `DEPLOYMENT.md` - Production deployment guide
- `DEPRECATION_NOTICE.md` - Pokemon TCG API deprecation details
- `COLLECTION_DECK_SYSTEM.md` - Collection/Deck system
- `TCGCSV_README.md` - TCGCSV integration
- `ARTICLES_SYSTEM.md` - Informational articles feature
- `ARTICLES_MULTILANGUAGE.md` - Multi-language article translation
- `STATUS.md` - This file (current status)
- `PROJECT_SNAPSHOT.md` - Project overview

---

## 🔗 Quick Reference

### Import TCGCSV Data
```bash
# Pokemon
php artisan tcgcsv:import --game=pokemon --only=all

# Magic: The Gathering
php artisan tcgcsv:import --game=mtg --only=all

# Yu-Gi-Oh
php artisan tcgcsv:import --game=yugioh --only=all
```

### Import Cardmarket Data
```bash
# Download and import
php artisan cardmarket:download
php artisan cardmarket:import

# Auto-match expansions and products
php artisan cardmarket:match-expansions --threshold=80 --auto-confirm
php artisan cardmarket:match-products --threshold=85 --auto-confirm
```

### Check Data Status
```bash
php artisan tinker
# TCGCSV
>>> Game::with('groups')->get()->map(fn($g) => [$g->name, $g->groups->count()])
>>> DB::table('tcgcsv_products')->count()

# Cardmarket
>>> DB::table('cardmarket_products')->count()
>>> DB::table('cardmarket_expansions')->whereNotNull('tcgcsv_group_id')->count()
```

### Switch Game (in code)
```php
// Get current game
$game = currentGame();

// Get game ID
$gameId = currentGameId();

// Get user's available games
$games = availableGames();
```

--- 

*This file is automatically updated with each significant change to the multi-game system.*
