# 🎴 Lorcana Implementation - Quick Start

**API**: CardMarket API via RapidAPI  
**Documentation**: https://rapidapi.com/tcggopro/api/cardmarket-api-tcg  
**Backend Name**: `cmapi` (shared with One Piece)  
**Game Slug**: `lorcana`

---

## 🚀 Setup Steps

### 1. Get RapidAPI Key

1. Go to https://rapidapi.com/tcggopro/api/cardmarket-api-tcg/pricing
2. Subscribe to a plan:
   - **Free**: 100 req/day (testing only)
   - **Pro** ($9.90/mo): 3,000 req/day ✅ Recommended
   - **Ultra** ($24.90/mo): 15,000 req/day (for full initial import)
3. Copy your API key from the dashboard

### 2. Environment Configuration

Add to `.env`:
```env
CMAPI_BASE_URL=https://cardmarket-api-tcg.p.rapidapi.com
CMAPI_RAPIDAPI_KEY=your_rapidapi_key_here
CMAPI_RAPIDAPI_HOST=cardmarket-api-tcg.p.rapidapi.com
CMAPI_TIMEOUT=30
```

### 3. Database Setup

```sql
-- Add game entry
INSERT INTO games (id, name, slug, tcgcsv_category_id, catalog_backend, created_at, updated_at)
VALUES (3, 'Disney Lorcana', 'lorcana', NULL, 'cmapi', NOW(), NOW());
```

### 4. Run Migrations

```bash
# Create cmapi tables migration (based on template in guide)
php artisan make:migration create_cmapi_tables

# Run migration
php artisan migrate
```

**Key tables**:
- `cmapi_sets` (with `cmapi_id`, `name`, `code`, `logo_url`, `release_date`, `card_count`, `raw`)
- `cmapi_cards` (with pricing, Lorcana-specific fields)
- `cmapi_import_runs`

**Lorcana-specific columns** to add to `cmapi_cards`:
```php
$table->integer('ink_cost')->nullable();
$table->string('card_type')->nullable(); // Character, Action, Item, Location
$table->integer('lore_value')->nullable();
$table->string('ink_color')->nullable(); // Amber, Amethyst, Emerald, Ruby, Sapphire, Steel
```

---

## 📁 Required Files

### 1. Config File

**File**: `config/cmapi.php`

```php
<?php

return [
    'base_url' => env('CMAPI_BASE_URL', 'https://cardmarket-api-tcg.p.rapidapi.com'),
    'timeout' => env('CMAPI_TIMEOUT', 30),
    'retry_count' => env('CMAPI_RETRY_COUNT', 3),
    'retry_sleep_ms' => env('CMAPI_RETRY_SLEEP_MS', 1000),
    
    'rapidapi_key' => env('CMAPI_RAPIDAPI_KEY'),
    'rapidapi_host' => env('CMAPI_RAPIDAPI_HOST', 'cardmarket-api-tcg.p.rapidapi.com'),
    
    'rate_limit_per_minute' => env('CMAPI_RATE_LIMIT_PER_MINUTE', 30),
];
```

### 2. API Client

**File**: `app/Services/Cmapi/CmapiClient.php`

See full template in [NEW_GAME_IMPLEMENTATION_GUIDE.md](NEW_GAME_IMPLEMENTATION_GUIDE.md) - Appendix A.

Key methods:
- `listSets()` → Fetches `/lorcana/episodes`
- `listCardsBySet($episodeId)` → Fetches `/lorcana/episodes/{id}/cards`
- `getCard($cardId)` → Fetches `/lorcana/cards/{id}`
- `normalizeSet()` → Converts API data to DB format
- `normalizeCard()` → Extracts pricing (divides by 100!)

### 3. Import Service

**File**: `app/Services/Cmapi/CmapiImportService.php`

Copy structure from `TcgdxImportService.php`, adjust for:
- Use `CmapiClient` instead of `TcgdxClient`
- Use `CmapiSet` and `CmapiCard` models
- Handle "episodes" terminology

### 4. Artisan Command

**File**: `app/Console/Commands/CmapiImportCommand.php`

```php
protected $signature = 'cmapi:import 
                        {--game=lorcana : Game slug (lorcana, onepiece)}
                        {--episode= : Import single episode/set}
                        {--cards-only : Skip sets, import cards only}
                        {--fresh : Truncate tables before import}';
```

---

## 🎯 API Key Points

### Endpoints Structure

```
GET /lorcana/episodes                    # List all sets
GET /lorcana/episodes/{id}/cards         # Cards in a set
GET /lorcana/cards/{id}                  # Single card detail
GET /lorcana/cards?search=elsa           # Search cards
```

### Headers Required

```php
[
    'Accept' => 'application/json',
    'X-RapidAPI-Key' => 'your_key',
    'X-RapidAPI-Host' => 'cardmarket-api-tcg.p.rapidapi.com',
]
```

### Response Structure (Card)

```json
{
  "id": "123",
  "name": "Elsa - Snow Queen",
  "name_numbered": "Elsa - Snow Queen 1",
  "number": "1",
  "rarity": "Legendary",
  "image_url": "https://...",
  "prices": {
    "cardmarket": {
      "currency": "EUR",
      "lowest_near_mint": 750,        // ⚠️ IN CENTS! = €7.50
      "30d_average": 650
    },
    "tcg_player": {
      "currency": "USD",
      "market_price": 850              // ⚠️ IN CENTS! = $8.50
    }
  },
  "ink_cost": 8,
  "card_type": "Character",
  "lore": 4,
  "color": "Sapphire"
}
```

**CRITICAL**: Prices are in **cents**, divide by 100!

---

## 💻 Import Commands

### Test API Connection

```bash
php artisan tinker

>>> $client = new \App\Services\Cmapi\CmapiClient('lorcana');
>>> $episodes = $client->listSets();
>>> count($episodes)  // Should return number of sets
```

### Initial Full Import

```bash
# Import all sets and cards
php artisan cmapi:import --game=lorcana

# With fresh start (deletes existing data)
php artisan cmapi:import --game=lorcana --fresh
```

### Incremental Updates

```bash
# Import only cards (sets already exist)
php artisan cmapi:import --game=lorcana --cards-only
```

### Single Set Import

```bash
# Import one specific set/episode
php artisan cmapi:import --game=lorcana --episode=1
```

---

## 🎨 Frontend Integration

### Routes

```php
// routes/web.php
Route::prefix('lorcana')->name('lorcana.')->group(function () {
    Route::get('/sets', [LorcanaCatalogController::class, 'sets'])->name('sets');
    Route::get('/sets/{episode}', [LorcanaCatalogController::class, 'setDetail'])->name('sets.show');
    Route::get('/cards/{card}', [LorcanaCatalogController::class, 'cardDetail'])->name('cards.show');
});
```

### Controller

```php
// app/Http/Controllers/Lorcana/CatalogController.php
use App\Models\Cmapi\CmapiSet;
use App\Models\Cmapi\CmapiCard;

public function sets()
{
    $sets = CmapiSet::orderBy('release_date', 'desc')->paginate(24);
    return view('lorcana.catalog.sets', ['sets' => $sets]);
}
```

### Views

```blade
{{-- resources/views/lorcana/catalog/sets.blade.php --}}
@foreach($sets as $set)
    <div class="set-card">
        <img src="{{ $set->logo_url }}" alt="{{ $set->name }}">
        <h3>{{ $set->name }}</h3>
        <p>{{ $set->card_count }} cards</p>
    </div>
@endforeach
```

---

## 📊 Scheduled Updates

Add to `routes/console.php`:

```php
// Import Lorcana cards daily at 3 AM (after Pokemon)
Schedule::command('cmapi:import --game=lorcana --cards-only')
    ->dailyAt('03:00')
    ->timezone('Europe/Copenhagen')
    ->withoutOverlapping()
    ->onOneServer();
```

---

## ⚠️ Important Notes

1. **Rate Limits**: 
   - Free tier: 30 req/min → Initial import will take time
   - Pro tier: 300 req/min → Much faster
   - Add delays if hitting limits: `sleep(1)` between requests

2. **Price Conversion**:
   ```php
   // API returns cents
   $priceEur = $cardData['prices']['cardmarket']['lowest_near_mint']; // 750
   $priceInEuro = $priceEur / 100; // 7.50
   ```

3. **Terminology**:
   - API uses "episodes" not "sets"
   - Your DB can still use `cmapi_sets` table name

4. **Reusable Backend**:
   - Same `CmapiClient` works for One Piece
   - Just change: `new CmapiClient('onepiece')`
   - Database schema is identical

5. **Error Handling**:
   - 429 = Rate limit exceeded → Add delays
   - 401 = Invalid API key → Check .env
   - 404 = Endpoint/resource not found

---

## 🔍 Testing Checklist

- [ ] RapidAPI key configured in .env
- [ ] Config file created (`config/cmapi.php`)
- [ ] Tables migrated successfully
- [ ] Game entry in `games` table
- [ ] API client test in tinker (fetch episodes)
- [ ] Import command runs without errors
- [ ] Sets imported correctly
- [ ] Cards imported with prices
- [ ] Prices divided by 100 correctly
- [ ] Routes working (`/lorcana/sets`)
- [ ] Views displaying cards
- [ ] Pricing gated by subscription
- [ ] Add to collection works
- [ ] Add to deck works

---

## 📚 Full Documentation

For complete implementation details, see:
- [NEW_GAME_IMPLEMENTATION_GUIDE.md](NEW_GAME_IMPLEMENTATION_GUIDE.md) - Full implementation guide
- [COMMON_ERRORS.md](COMMON_ERRORS.md) - Common pitfalls to avoid

---

**Ready to implement Lorcana!** 🎉

Start with Phase 1 (Database Setup) in the main guide and work through systematically.
