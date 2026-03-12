# 🎮 New Game Implementation Guide

**Reference Document for Adding MTG / YGO / Lorcana**

*Created: January 31, 2026*  
*Updated: February 1, 2026*  
*Based on: Pokemon/TCGDEX implementation (Jan 28-31, 2026) + UX improvements (Feb 1, 2026)*

---

## 📋 Overview

This guide provides a step-by-step checklist to implement a new card game (Magic: The Gathering, Yu-Gi-Oh!, Lorcana) following the proven TCGDEX pattern already implemented for Pokemon.

### Key Pattern: Dual-Backend Architecture
- Each game has its own staging tables (`{game}_sets`, `{game}_cards`)
- User interaction tables support all backends via nullable foreign keys
- Backend selection via `games.catalog_backend` column
- Pricing from Cardmarket (EU) + TCGPlayer (US)

---

## 🎯 Prerequisites

Before starting, decide:
1. **Game to implement**: [ ] MTG  [ ] YGO  [ ] Lorcana  [ ] One Piece
2. **API Source**: 
   - MTG: Scryfall API (https://scryfall.com/docs/api)
   - YGO: YGOPRODeck API (https://ygoprodeck.com/api-guide/)
   - Lorcana: CardMarket API via RapidAPI (https://rapidapi.com/tcggopro/api/cardmarket-api-tcg)
   - One Piece: CardMarket API via RapidAPI (same as Lorcana)
3. **Backend code name**: `scryfall` / `ygoprodeck` / `cmapi` (CardMarket API)
4. **API Authentication**:
   - Scryfall: None required (rate limited)
   - YGOPRODeck: None required
   - CardMarket API: RapidAPI key required (query param or X-RapidAPI-Key header)
5. **Rate Limits** (CardMarket API):
   - Basic (Free): 100 req/day, 30 req/min
   - Pro ($9.90/mo): 3,000 req/day, 300 req/min
   - Ultra ($24.90/mo): 15,000 req/day, 300 req/min
   - Mega ($49.50/mo): 50,000 req/day, 600 req/min

---

## 📁 Phase 1: Database Setup

### 1.1 Create Game Entry
**File**: Database seeder or manual SQL  
**Table**: `games`

```sql
-- Example for MTG
INSERT INTO games (id, name, slug, tcgcsv_category_id, catalog_backend, created_at, updated_at)
VALUES (2, 'Magic: The Gathering', 'magic', 1, 'scryfall', NOW(), NOW());

-- Example for Lorcana (using CardMarket API)
INSERT INTO games (id, name, slug, tcgcsv_category_id, catalog_backend, created_at, updated_at)
VALUES (3, 'Disney Lorcana', 'lorcana', NULL, 'cmapi', NOW(), NOW());

-- Example for One Piece (using CardMarket API)
INSERT INTO games (id, name, slug, tcgcsv_category_id, catalog_backend, created_at, updated_at)
VALUES (4, 'One Piece', 'onepiece', NULL, 'cmapi', NOW(), NOW());
```

**Checklist**:
- [ ] Insert game record with unique `id`
- [ ] Set correct `slug` (used in routes: `/magic/*`)
- [ ] Define `catalog_backend` value (will be used in code)
- [ ] Verify `tcgcsv_category_id` if keeping TCGCSV as fallback

---

### 1.2 Create Staging Tables

**Reference**: See `database/migrations/2026_01_31_*_create_tcgdx_tables.php`  
**Pattern**: `{backend}_sets` and `{backend}_cards`

#### Migration Template:

```php
// database/migrations/YYYY_MM_DD_create_{backend}_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sets table
        Schema::create('{backend}_sets', function (Blueprint $table) {
            $table->id();
            $table->string('{backend}_id')->unique()->index(); // API set ID
            $table->string('name');
            $table->string('code')->nullable()->index(); // Set code (e.g., "MH3")
            $table->string('logo_url')->nullable();
            $table->date('release_date')->nullable();
            $table->integer('card_count')->nullable();
            $table->json('raw')->nullable(); // Full API response
            $table->timestamps();
        });

        // Cards table
        Schema::create('{backend}_cards', function (Blueprint $table) {
            $table->id();
            $table->string('{backend}_id')->unique()->index(); // API card ID
            $table->unsignedBigInteger('set_{backend}_id')->index();
            $table->string('name');
            $table->string('number')->nullable();
            $table->string('rarity')->nullable();
            $table->string('image_small_url')->nullable();
            $table->string('image_large_url')->nullable();
            $table->decimal('price_eur', 10, 2)->nullable()->index();
            $table->decimal('price_usd', 10, 2)->nullable()->index();
            $table->json('raw')->nullable(); // Full API response
            $table->timestamps();
            
            $table->foreign('set_{backend}_id')
                  ->references('id')
                  ->on('{backend}_sets')
                  ->onDelete('cascade');
        });

        // Import runs tracking
        Schema::create('{backend}_import_runs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('status')->default('running'); // running, success, failed
            $table->string('scope')->default('all'); // all, single_set
            $table->json('stats')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{backend}_cards');
        Schema::dropIfExists('{backend}_sets');
        Schema::dropIfExists('{backend}_import_runs');
    }
};
```

**Checklist**:
- [ ] Create migration file
- [ ] Replace `{backend}` with actual backend name (e.g., `scryfall`)
- [ ] Add game-specific columns if needed (e.g., MTG colors, YGO type)
- [ ] Run migration: `php artisan migrate`
- [ ] Verify tables exist: `php artisan db:show`

---

### 1.3 Extend User Interaction Tables

**Reference**: See `database/migrations/*_add_tcgdex_card_id_to_*.php`  
**Tables to extend**: 
- `user_collection`
- `deck_cards`
- `user_likes`
- `user_wishlist_items`
- `user_watch_items`

#### Migration Template:

```php
// database/migrations/YYYY_MM_DD_add_{backend}_card_id_to_user_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // User Collection
        Schema::table('user_collection', function (Blueprint $table) {
            $table->unsignedBigInteger('{backend}_card_id')->nullable()->after('tcgdex_card_id');
            $table->foreign('{backend}_card_id')
                  ->references('id')
                  ->on('{backend}_cards')
                  ->onDelete('cascade');
            $table->index('{backend}_card_id');
        });

        // Deck Cards
        Schema::table('deck_cards', function (Blueprint $table) {
            $table->unsignedBigInteger('{backend}_card_id')->nullable()->after('tcgdex_card_id');
            $table->foreign('{backend}_card_id')
                  ->references('id')
                  ->on('{backend}_cards')
                  ->onDelete('cascade');
            $table->index('{backend}_card_id');
        });

        // User Likes
        Schema::table('user_likes', function (Blueprint $table) {
            $table->unsignedBigInteger('{backend}_card_id')->nullable()->after('tcgdex_card_id');
            $table->foreign('{backend}_card_id')
                  ->references('id')
                  ->on('{backend}_cards')
                  ->onDelete('cascade');
            $table->index('{backend}_card_id');
        });

        // User Wishlist
        Schema::table('user_wishlist_items', function (Blueprint $table) {
            $table->unsignedBigInteger('{backend}_card_id')->nullable()->after('tcgdex_card_id');
            $table->foreign('{backend}_card_id')
                  ->references('id')
                  ->on('{backend}_cards')
                  ->onDelete('cascade');
            $table->index('{backend}_card_id');
        });

        // User Watch
        Schema::table('user_watch_items', function (Blueprint $table) {
            $table->unsignedBigInteger('{backend}_card_id')->nullable()->after('tcgdex_card_id');
            $table->foreign('{backend}_card_id')
                  ->references('id')
                  ->on('{backend}_cards')
                  ->onDelete('cascade');
            $table->index('{backend}_card_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_collection', function (Blueprint $table) {
            $table->dropForeign(['{backend}_card_id']);
            $table->dropIndex(['{backend}_card_id']);
            $table->dropColumn('{backend}_card_id');
        });

        Schema::table('deck_cards', function (Blueprint $table) {
            $table->dropForeign(['{backend}_card_id']);
            $table->dropIndex(['{backend}_card_id']);
            $table->dropColumn('{backend}_card_id');
        });

        Schema::table('user_likes', function (Blueprint $table) {
            $table->dropForeign(['{backend}_card_id']);
            $table->dropIndex(['{backend}_card_id']);
            $table->dropColumn('{backend}_card_id');
        });

        Schema::table('user_wishlist_items', function (Blueprint $table) {
            $table->dropForeign(['{backend}_card_id']);
            $table->dropIndex(['{backend}_card_id']);
            $table->dropColumn('{backend}_card_id');
        });

        Schema::table('user_watch_items', function (Blueprint $table) {
            $table->dropForeign(['{backend}_card_id']);
            $table->dropIndex(['{backend}_card_id']);
            $table->dropColumn('{backend}_card_id');
        });
    }
};
```

**Checklist**:
- [ ] Create migration
- [ ] Replace `{backend}` everywhere
- [ ] Run migration: `php artisan migrate`
- [ ] Verify columns added with proper indexes and foreign keys

---

## 📁 Phase 2: Models

### 2.1 Create Backend Models

**Reference**: `app/Models/Tcgdx/TcgdxCard.php`, `app/Models/Tcgdx/TcgdxSet.php`

#### Set Model Template:

```php
// app/Models/{Backend}/{Backend}Set.php

<?php

namespace App\Models\{Backend};

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class {Backend}Set extends Model
{
    protected $table = '{backend}_sets';

    protected $fillable = [
        '{backend}_id',
        'name',
        'code',
        'logo_url',
        'release_date',
        'card_count',
        'raw',
    ];

    protected $casts = [
        'raw' => 'array',
        'release_date' => 'date',
        'card_count' => 'integer',
    ];

    /**
     * Cards in this set
     */
    public function cards(): HasMany
    {
        return $this->hasMany({Backend}Card::class, 'set_{backend}_id');
    }
}
```

#### Card Model Template:

```php
// app/Models/{Backend}/{Backend}Card.php

<?php

namespace App\Models\{Backend};

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class {Backend}Card extends Model
{
    protected $table = '{backend}_cards';

    protected $fillable = [
        '{backend}_id',
        'set_{backend}_id',
        'name',
        'number',
        'rarity',
        'image_small_url',
        'image_large_url',
        'price_eur',
        'price_usd',
        'raw',
    ];

    protected $casts = [
        'raw' => 'array',
        'price_eur' => 'decimal:2',
        'price_usd' => 'decimal:2',
    ];

    /**
     * Set this card belongs to
     */
    public function set(): BelongsTo
    {
        return $this->belongsTo({Backend}Set::class, 'set_{backend}_id');
    }
}
```

#### Import Run Model Template:

```php
// app/Models/{Backend}/{Backend}ImportRun.php

<?php

namespace App\Models\{Backend};

use Illuminate\Database\Eloquent\Model;

class {Backend}ImportRun extends Model
{
    protected $table = '{backend}_import_runs';

    protected $fillable = [
        'started_at',
        'finished_at',
        'status',
        'scope',
        'stats',
        'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'stats' => 'array',
    ];

    public static function start(string $scope, array $initialStats = []): self
    {
        return self::create([
            'started_at' => now(),
            'status' => 'running',
            'scope' => $scope,
            'stats' => $initialStats,
        ]);
    }

    public function markAsSuccess(array $stats = []): void
    {
        $this->update([
            'status' => 'success',
            'finished_at' => now(),
            'stats' => $stats,
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'finished_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }

    public function addStats(array $stats): void
    {
        $this->update([
            'stats' => array_merge($this->stats ?? [], $stats),
        ]);
    }
}
```

**Checklist**:
- [ ] Create `app/Models/{Backend}/` directory
- [ ] Create {Backend}Set model
- [ ] Create {Backend}Card model
- [ ] Create {Backend}ImportRun model
- [ ] Replace all `{Backend}` and `{backend}` placeholders
- [ ] Test models load: `php artisan tinker` → `App\Models\{Backend}\{Backend}Card::count()`

---

### 2.2 Extend User Models

**Reference**: `app/Models/UserCollection.php`, `app/Models/DeckCard.php`

Add relationships to existing models:

#### UserCollection

```php
// app/Models/UserCollection.php - ADD this method

/**
 * {Backend} card relationship (if using {backend} catalog)
 */
public function {backend}Card(): BelongsTo
{
    return $this->belongsTo(\App\Models\{Backend}\{Backend}Card::class, '{backend}_card_id');
}
```

#### DeckCard

```php
// app/Models/DeckCard.php - ADD this method

/**
 * {Backend} card relationship (if using {backend} catalog)
 */
public function {backend}Card(): BelongsTo
{
    return $this->belongsTo(\App\Models\{Backend}\{Backend}Card::class, '{backend}_card_id');
}
```

#### UserLike

```php
// app/Models/UserLike.php - ADD this method

/**
 * {Backend} card relationship
 */
public function {backend}Card(): BelongsTo
{
    return $this->belongsTo(\App\Models\{Backend}\{Backend}Card::class, '{backend}_card_id');
}
```

#### UserWishlistItem

```php
// app/Models/UserWishlistItem.php - ADD this method

/**
 * {Backend} card relationship
 */
public function {backend}Card(): BelongsTo
{
    return $this->belongsTo(\App\Models\{Backend}\{Backend}Card::class, '{backend}_card_id');
}
```

#### UserWatchItem

```php
// app/Models/UserWatchItem.php - ADD this method

/**
 * {Backend} card relationship
 */
public function {backend}Card(): BelongsTo
{
    return $this->belongsTo(\App\Models\{Backend}\{Backend}Card::class, '{backend}_card_id');
}
```

**Checklist**:
- [ ] Add `{backend}Card()` method to UserCollection
- [ ] Add `{backend}Card()` method to DeckCard
- [ ] Add `{backend}Card()` method to UserLike
- [ ] Add `{backend}Card()` method to UserWishlistItem
- [ ] Add `{backend}Card()` method to UserWatchItem

---

## 📁 Phase 3: API Client & Import Service

### 3.1 Create API Client

**Reference**: `app/Services/Tcgdx/TcgdxClient.php`

```php
// app/Services/{Backend}/{Backend}Client.php

<?php

namespace App\Services\{Backend};

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

/**
 * {Backend} API Client
 * 
 * Documentation: [INSERT API DOCS URL]
 */
class {Backend}Client
{
    protected string $baseUrl;
    protected int $timeout;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('{backend}.base_url');
        $this->timeout = config('{backend}.timeout', 30);
        $this->apiKey = config('{backend}.api_key');
    }

    /**
     * Fetch all sets
     */
    public function listSets(): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/sets");

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch sets: {$response->status()}");
        }

        return $response->json();
    }

    /**
     * Fetch single set with details
     */
    public function getSet(string $setId): ?array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/sets/{$setId}");

        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Fetch cards for a set
     */
    public function listCardsBySet(string $setId): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/sets/{$setId}/cards");

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch cards for set {$setId}");
        }

        return $response->json();
    }

    /**
     * Fetch single card details
     */
    public function getCard(string $cardId): ?array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/cards/{$cardId}");

        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Normalize set data for database
     */
    public function normalizeSet(array $setData): array
    {
        return [
            '{backend}_id' => $setData['id'],
            'name' => $setData['name'],
            'code' => $setData['code'] ?? null,
            'logo_url' => $setData['icon_svg_uri'] ?? null,
            'release_date' => $setData['released_at'] ?? null,
            'card_count' => $setData['card_count'] ?? null,
            'raw' => $setData,
        ];
    }

    /**
     * Normalize card data for database
     */
    public function normalizeCard(array $cardData, int $setDbId): array
    {
        if (!$setDbId) {
            throw new \Exception("Invalid set_id: cannot be null or 0");
        }

        // Extract prices
        $priceEur = $this->extractEurPrice($cardData);
        $priceUsd = $this->extractUsdPrice($cardData);

        return [
            '{backend}_id' => $cardData['id'],
            'set_{backend}_id' => $setDbId,
            'name' => $cardData['name'],
            'number' => $cardData['collector_number'] ?? null,
            'rarity' => $cardData['rarity'] ?? null,
            'image_small_url' => $cardData['image_uris']['small'] ?? null,
            'image_large_url' => $cardData['image_uris']['normal'] ?? null,
            'price_eur' => $priceEur,
            'price_usd' => $priceUsd,
            'raw' => $cardData,
        ];
    }

    /**
     * Extract EUR price from API data
     */
    protected function extractEurPrice(array $cardData): ?float
    {
        // TODO: Implement based on API structure
        // Example for Scryfall: $cardData['prices']['eur']
        return null;
    }

    /**
     * Extract USD price from API data
     */
    protected function extractUsdPrice(array $cardData): ?float
    {
        // TODO: Implement based on API structure
        // Example for Scryfall: $cardData['prices']['usd']
        return null;
    }

    /**
     * Get request headers
     */
    protected function getHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
        ];

        if ($this->apiKey) {
            $headers['Authorization'] = "Bearer {$this->apiKey}";
        }

        return $headers;
    }
}
```

#### CardMarket API Client Template (for Lorcana/One Piece via RapidAPI):

```php
// app/Services/Cmapi/CmapiClient.php

<?php

namespace App\Services\Cmapi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CardMarket API Client (via RapidAPI)
 * 
 * Documentation: https://rapidapi.com/tcggopro/api/cardmarket-api-tcg
 * Supported games: pokemon, lorcana, onepiece
 * 
 * Endpoints:
 * - GET /{game}/episodes - List all sets ("episodes")
 * - GET /{game}/episodes/{id}/cards - List cards in a set
 * - GET /{game}/cards/{id} - Get single card
 * - GET /{game}/cards?search={query} - Search cards
 */
class CmapiClient
{
    protected string $baseUrl;
    protected int $timeout;
    protected string $rapidApiKey;
    protected string $rapidApiHost;
    protected string $game; // 'lorcana', 'onepiece', etc.

    public function __construct(string $game = 'lorcana')
    {
        $this->baseUrl = config('cmapi.base_url');
        $this->timeout = config('cmapi.timeout', 30);
        $this->rapidApiKey = config('cmapi.rapidapi_key');
        $this->rapidApiHost = config('cmapi.rapidapi_host');
        $this->game = $game;
    }

    /**
     * Fetch all sets (called "episodes" in CMAPI)
     */
    public function listSets(): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/{$this->game}/episodes");

        if (!$response->successful()) {
            Log::error("CMAPI listSets failed: {$response->status()}", [
                'body' => $response->body(),
            ]);
            throw new \Exception("Failed to fetch sets: {$response->status()}");
        }

        return $response->json() ?? [];
    }

    /**
     * Fetch single set details
     */
    public function getSet(string $episodeId): ?array
    {
        // CMAPI doesn't have dedicated episode detail endpoint
        // Get all episodes and filter by ID
        $episodes = $this->listSets();
        
        foreach ($episodes as $episode) {
            if (($episode['id'] ?? null) == $episodeId) {
                return $episode;
            }
        }
        
        return null;
    }

    /**
     * Fetch cards for a set/episode
     */
    public function listCardsBySet(string $episodeId): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/{$this->game}/episodes/{$episodeId}/cards");

        if (!$response->successful()) {
            Log::error("CMAPI listCardsBySet failed: {$response->status()}", [
                'episode_id' => $episodeId,
                'body' => $response->body(),
            ]);
            throw new \Exception("Failed to fetch cards for episode {$episodeId}");
        }

        return $response->json() ?? [];
    }

    /**
     * Fetch single card details
     */
    public function getCard(string $cardId): ?array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders($this->getHeaders())
            ->get("{$this->baseUrl}/{$this->game}/cards/{$cardId}");

        if (!$response->successful()) {
            Log::warning("CMAPI getCard failed: {$response->status()}", [
                'card_id' => $cardId,
            ]);
            return null;
        }

        return $response->json();
    }

    /**
     * Normalize set/episode data for database
     */
    public function normalizeSet(array $episodeData): array
    {
        return [
            'cmapi_id' => $episodeData['id'],
            'name' => $episodeData['name'] ?? $episodeData['title'] ?? 'Unknown',
            'code' => $episodeData['code'] ?? $episodeData['slug'] ?? null,
            'logo_url' => $episodeData['logo_url'] ?? $episodeData['image_url'] ?? null,
            'release_date' => $episodeData['release_date'] ?? $episodeData['released_at'] ?? null,
            'card_count' => $episodeData['card_count'] ?? $episodeData['total_cards'] ?? null,
            'raw' => $episodeData,
        ];
    }

    /**
     * Normalize card data for database
     */
    public function normalizeCard(array $cardData, int $setDbId): array
    {
        if (!$setDbId) {
            throw new \Exception("Invalid set_id: cannot be null or 0");
        }

        // Extract pricing from nested structure
        $priceEur = $this->extractPrice($cardData, 'cardmarket', 'lowest_near_mint');
        $priceUsd = $this->extractPrice($cardData, 'tcg_player', 'market_price');

        return [
            'cmapi_id' => $cardData['id'],
            'set_cmapi_id' => $setDbId,
            'name' => $cardData['name'],
            'number' => $cardData['number'] ?? $cardData['card_number'] ?? null,
            'rarity' => $cardData['rarity'] ?? null,
            'image_small_url' => $cardData['image_url'] ?? $cardData['images']['small'] ?? null,
            'image_large_url' => $cardData['image_url_hires'] ?? $cardData['images']['large'] ?? null,
            'price_eur' => $priceEur,
            'price_usd' => $priceUsd,
            'raw' => $cardData,
        ];
    }

    /**
     * Extract price from CMAPI nested pricing structure
     * 
     * Example structure:
     * {
     *   "prices": {
     *     "cardmarket": {
     *       "currency": "EUR",
     *       "lowest_near_mint": 750,
     *       "30d_average": 192.79
     *     },
     *     "tcg_player": {
     *       "currency": "USD",
     *       "market_price": 146.69
     *     }
     *   }
     * }
     */
    protected function extractPrice(array $cardData, string $marketplace, string $priceKey): ?float
    {
        // Check nested prices structure
        if (isset($cardData['prices'][$marketplace][$priceKey])) {
            $price = $cardData['prices'][$marketplace][$priceKey];
            // CMAPI returns prices in cents, convert to decimal
            return $price ? round($price / 100, 2) : null;
        }

        return null;
    }

    /**
     * Get RapidAPI headers
     */
    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'X-RapidAPI-Key' => $this->rapidApiKey,
            'X-RapidAPI-Host' => $this->rapidApiHost,
        ];
    }
}
```

**Checklist**:
- [ ] Create `app/Services/{Backend}/` directory
- [ ] Create {Backend}Client class
- [ ] Update API endpoints based on actual API documentation
- [ ] Implement `normalizeSet()` based on API response structure
- [ ] Implement `normalizeCard()` based on API response structure
- [ ] Implement `extractEurPrice()` and `extractUsdPrice()`
- [ ] Test API calls: `php artisan tinker` → Test client methods

---

### 3.2 Create Import Service

**Reference**: `app/Services/Tcgdx/TcgdxImportService.php`

```php
// app/Services/{Backend}/{Backend}ImportService.php

<?php

namespace App\Services\{Backend};

use App\Models\{Backend}\{Backend}Card;
use App\Models\{Backend}\{Backend}ImportRun;
use App\Models\{Backend}\{Backend}Set;
use Illuminate\Support\Facades\DB;
use Throwable;

class {Backend}ImportService
{
    protected {Backend}Client $client;
    
    public function __construct({Backend}Client $client)
    {
        $this->client = $client;
    }

    /**
     * Import only cards for existing sets
     */
    public function runImportCardsOnly(?callable $output = null, $pipelineRun = null): array
    {
        $allSets = {Backend}Set::all();
        $cardsTotal = 0;
        
        if ($output) {
            $output("🎴 Importing cards for {$allSets->count()} existing sets...\n\n");
        }
        
        foreach ($allSets as $index => $set) {
            $progress = $index + 1;
            $total = $allSets->count();
            
            if ($output) {
                $output("[$progress/$total] Importing cards for set: {$set->{backend}_id}...\n");
            }

            try {
                $result = $this->importCardsForSet($set, $output);
                $cardsTotal += $result['cards_imported'] ?? 0;
                
                if ($output) {
                    $output("  ✅ {$result['cards_imported']} cards imported\n\n");
                }
                
                if ($pipelineRun && $progress % 20 === 0) {
                    $pipelineRun->updateStats([
                        'rows_created' => $cardsTotal,
                    ]);
                }
            } catch (Throwable $e) {
                if ($output) {
                    $output("  ❌ Failed importing cards: {$e->getMessage()}\n\n");
                }
            }
        }
        
        return [
            'cards_total' => $cardsTotal,
        ];
    }

    /**
     * Import all sets and their cards
     */
    public function runImportAll(?callable $output = null, $pipelineRun = null): {Backend}ImportRun
    {
        $run = {Backend}ImportRun::start('all', [
            'sets_total' => 0,
            'sets_imported' => 0,
            'sets_failed' => 0,
            'cards_total' => 0,
            'failed_sets' => [],
        ]);

        try {
            if ($output) {
                $output("🚀 Fetching sets from {Backend}...\n");
            }

            $sets = $this->client->listSets();
            $totalSets = count($sets);
            
            $run->addStats(['sets_total' => $totalSets]);

            if ($output) {
                $output("📦 Found {$totalSets} sets\n\n");
            }

            $setsImported = 0;
            $setsFailed = 0;
            $cardsTotal = 0;
            $failedSets = [];
            $importedSetIds = [];

            // Phase 1: Import all sets
            if ($output) {
                $output("📦 Phase 1: Importing sets...\n\n");
            }

            foreach ($sets as $index => $setData) {
                $setId = $setData['id'] ?? null;
                
                if (!$setId) {
                    continue;
                }

                $progress = $index + 1;
                
                if ($output) {
                    $output("[$progress/$totalSets] Importing set: {$setId}...\n");
                }

                try {
                    $setDataFull = $this->client->getSet($setId);
                    if ($setDataFull) {
                        $normalizedSet = $this->client->normalizeSet($setDataFull);
                        {Backend}Set::updateOrCreate(
                            ['{backend}_id' => $normalizedSet['{backend}_id']],
                            $normalizedSet
                        );
                        $setsImported++;
                        $importedSetIds[] = $setId;
                        if ($output) {
                            $output("  ✅ Set imported\n\n");
                        }
                    }
                } catch (Throwable $e) {
                    $setsFailed++;
                    $failedSets[] = [
                        'set_id' => $setId,
                        'error' => $e->getMessage(),
                    ];
                    if ($output) {
                        $output("  ❌ Failed: {$e->getMessage()}\n\n");
                    }
                }
            }

            // Phase 2: Import cards for successfully imported sets
            if ($output) {
                $output("\n🎴 Phase 2: Importing cards...\n\n");
            }

            $importedSets = {Backend}Set::whereIn('{backend}_id', $importedSetIds)->get();
            foreach ($importedSets as $index => $set) {
                $progress = $index + 1;
                $total = $importedSets->count();
                
                if ($output) {
                    $output("[$progress/$total] Importing cards for set: {$set->{backend}_id}...\n");
                }

                try {
                    $result = $this->importCardsForSet($set, $output);
                    $cardsTotal += $result['cards_imported'] ?? 0;
                    
                    if ($output) {
                        $output("  ✅ {$result['cards_imported']} cards imported\n\n");
                    }
                    
                    if ($pipelineRun && $progress % 20 === 0) {
                        $pipelineRun->updateStats([
                            'rows_processed' => $setsImported,
                            'rows_created' => $cardsTotal,
                            'errors_count' => $setsFailed,
                        ]);
                    }
                } catch (Throwable $e) {
                    if ($output) {
                        $output("  ❌ Failed importing cards: {$e->getMessage()}\n\n");
                    }
                }
            }

            // Determine success/failure
            $failureRate = $totalSets > 0 ? ($setsFailed / $totalSets) : 0;
            $isSuccess = $failureRate < 0.20;

            $stats = [
                'sets_total' => $totalSets,
                'sets_imported' => $setsImported,
                'sets_failed' => $setsFailed,
                'cards_total' => $cardsTotal,
                'failed_sets' => $failedSets,
            ];

            if ($isSuccess) {
                $run->markAsSuccess($stats);
                if ($output) {
                    $output("✅ Import completed successfully!\n");
                    $output("   Sets: {$setsImported}/{$totalSets}\n");
                    $output("   Cards: {$cardsTotal}\n");
                }
            } else {
                $run->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'stats' => $stats,
                    'error_message' => "Too many sets failed: {$setsFailed}/{$totalSets}",
                ]);
                if ($output) {
                    $output("❌ Import failed: too many sets failed ({$setsFailed}/{$totalSets})\n");
                }
            }

        } catch (Throwable $e) {
            $run->markAsFailed($e->getMessage());
            
            if ($output) {
                $output("❌ Import failed: {$e->getMessage()}\n");
            }
        }

        return $run;
    }

    /**
     * Import a single set and its cards
     */
    public function importSet(string $setId, ?callable $output = null): array
    {
        $setData = $this->client->getSet($setId);
        
        if (!$setData) {
            throw new \Exception("Set not found: {$setId}");
        }

        $normalizedSet = $this->client->normalizeSet($setData);
        
        $set = {Backend}Set::updateOrCreate(
            ['{backend}_id' => $normalizedSet['{backend}_id']],
            $normalizedSet
        );

        $result = $this->importCardsForSet($set, $output);
        
        return [
            'set_id' => $set->id,
            'cards_imported' => $result['cards_imported'],
        ];
    }

    /**
     * Import cards for a specific set
     */
    public function importCardsForSet({Backend}Set $set, ?callable $output = null): array
    {
        if (!$set->id) {
            throw new \Exception("Set {$set->{backend}_id} does not have a database ID");
        }
        
        $cardSummaries = $this->client->listCardsBySet($set->{backend}_id);
        $cardsImported = 0;

        foreach ($cardSummaries as $cardSummary) {
            $cardId = $cardSummary['id'] ?? null;
            
            if (!$cardId) {
                continue;
            }

            $cardData = $this->client->getCard($cardId);
            
            if (!$cardData) {
                if ($output) {
                    $output("  ⚠️  Skipping card {$cardId}: not found\n");
                }
                continue;
            }

            $normalizedCard = $this->client->normalizeCard($cardData, $set->id);
            
            {Backend}Card::updateOrCreate(
                ['{backend}_id' => $normalizedCard['{backend}_id']],
                $normalizedCard
            );
            
            $cardsImported++;
        }

        return [
            'cards_imported' => $cardsImported,
        ];
    }
}
```

**Checklist**:
- [ ] Create {Backend}ImportService class
- [ ] Replace all `{Backend}` and `{backend}` placeholders
- [ ] Verify 2-phase import logic (sets → cards)
- [ ] Test service: `php artisan tinker` → Test import methods

---

### 3.3 Create Config File

**Reference**: `config/tcgdx.php`

```php
// config/{backend}.php

<?php

return [
    'base_url' => env('{BACKEND_UPPERCASE}_BASE_URL', 'https://api.example.com'),
    'timeout' => env('{BACKEND_UPPERCASE}_TIMEOUT', 30),
    'retry_count' => env('{BACKEND_UPPERCASE}_RETRY_COUNT', 3),
    'retry_sleep_ms' => env('{BACKEND_UPPERCASE}_RETRY_SLEEP_MS', 1000),
    'api_key' => env('{BACKEND_UPPERCASE}_API_KEY'),
];
```

#### CardMarket API Config Example (for Lorcana/One Piece):

```php
// config/cmapi.php

<?php

return [
    'base_url' => env('CMAPI_BASE_URL', 'https://cardmarket-api-tcg.p.rapidapi.com'),
    'timeout' => env('CMAPI_TIMEOUT', 30),
    'retry_count' => env('CMAPI_RETRY_COUNT', 3),
    'retry_sleep_ms' => env('CMAPI_RETRY_SLEEP_MS', 1000),
    
    // RapidAPI authentication
    'rapidapi_key' => env('CMAPI_RAPIDAPI_KEY'),
    'rapidapi_host' => env('CMAPI_RAPIDAPI_HOST', 'cardmarket-api-tcg.p.rapidapi.com'),
    
    // Rate limiting (Free tier: 100 req/day, 30 req/min)
    'rate_limit_per_minute' => env('CMAPI_RATE_LIMIT_PER_MINUTE', 30),
];
```

**Add to .env**:
```env
{BACKEND_UPPERCASE}_BASE_URL=https://api.example.com
{BACKEND_UPPERCASE}_API_KEY=your_key_here

# For CardMarket API via RapidAPI (Lorcana, One Piece):
CMAPI_BASE_URL=https://cardmarket-api-tcg.p.rapidapi.com
CMAPI_RAPIDAPI_KEY=your_rapidapi_key_here
CMAPI_RAPIDAPI_HOST=cardmarket-api-tcg.p.rapidapi.com
CMAPI_TIMEOUT=30
```

**Checklist**:
- [ ] Create config file
- [ ] Add environment variables to `.env.example`
- [ ] Add actual values to `.env`

---

## 📁 Phase 4: Artisan Commands

### 4.1 Create Import Command

**Reference**: `app/Console/Commands/TcgdxImportCommand.php`

```php
// app/Console/Commands/{Backend}ImportCommand.php

<?php

namespace App\Console\Commands;

use App\Models\PipelineRun;
use App\Models\{Backend}\{Backend}Card;
use App\Models\{Backend}\{Backend}ImportRun;
use App\Models\{Backend}\{Backend}Set;
use App\Services\{Backend}\{Backend}ImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class {Backend}ImportCommand extends Command
{
    protected $signature = '{backend}:import 
                            {--set= : Import only one set by {backend} id}
                            {--fresh : Truncate {backend} tables before import}
                            {--cards-only : Import only cards for existing sets, skip set import}';

    protected $description = 'Import {Game} sets and cards from {Backend} API';

    public function handle({Backend}ImportService $service): int
    {
        $pipelineRun = PipelineRun::start('{backend}:import');

        $this->info('🎴 {Backend} Import');
        $this->newLine();

        if ($this->option('fresh')) {
            $this->warn('⚠️  Fresh mode: truncating tables...');
            
            if (!$this->confirm('This will delete all {Backend} data. Continue?')) {
                $this->info('Cancelled.');
                return self::SUCCESS;
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            {Backend}Card::truncate();
            {Backend}Set::truncate();
            {Backend}ImportRun::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->info('✅ Tables truncated');
            $this->newLine();
        }

        $setId = $this->option('set');
        $cardsOnly = $this->option('cards-only');
        
        if ($setId) {
            $this->info("📦 Importing set: {$setId}");
            $this->newLine();
            
            try {
                $result = $service->importSet($setId, function($message) {
                    $this->line($message);
                });
                
                $this->newLine();
                $this->info("✅ Set imported successfully!");
                $this->line("   Cards: {$result['cards_imported']}");
                
                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error("❌ Failed: {$e->getMessage()}");
                return self::FAILURE;
            }
        }

        if ($cardsOnly) {
            $this->info('🎴 Importing cards only (sets already exist)');
            $this->newLine();
            
            try {
                $result = $service->runImportCardsOnly(function($message) {
                    $this->line($message);
                }, $pipelineRun);
                
                $this->newLine();
                $this->info('✅ Cards import completed!');
                $this->line("   Total Cards: {$result['cards_total']}");
                
                $pipelineRun->markSuccess([
                    'rows_created' => $result['cards_total'],
                ]);
                
                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error("❌ Failed: {$e->getMessage()}");
                $pipelineRun->markFailed($e->getMessage());
                return self::FAILURE;
            }
        }

        $run = $service->runImportAll(function($message) {
            $this->line($message);
        }, $pipelineRun);

        $this->newLine();
        
        if ($run->status === 'success') {
            $stats = $run->stats;
            $this->info('✅ Import completed successfully!');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Sets Imported', $stats['sets_imported'] ?? 0],
                    ['Sets Failed', $stats['sets_failed'] ?? 0],
                    ['Total Cards', $stats['cards_total'] ?? 0],
                ]
            );
            
            if (!empty($stats['failed_sets'])) {
                $this->warn('Failed sets:');
                foreach ($stats['failed_sets'] as $failed) {
                    $this->line("  - {$failed['set_id']}: {$failed['error']}");
                }
            }
            
            $pipelineRun->markSuccess([
                'rows_processed' => $stats['sets_imported'] ?? 0,
                'rows_created' => $stats['cards_total'] ?? 0,
                'errors_count' => $stats['sets_failed'] ?? 0,
            ]);
            
            return self::SUCCESS;
        }

        $this->error('❌ Import failed');
        $this->line("Error: {$run->error_message}");
        
        $pipelineRun->markFailed($run->error_message ?? 'Import failed');
        
        return self::FAILURE;
    }
}
```

**Checklist**:
- [ ] Create import command
- [ ] Replace all placeholders
- [ ] Register in `app/Console/Kernel.php` if needed
- [ ] Test command: `php artisan {backend}:import --help`

---

### 4.2 Add to Scheduler

**File**: `routes/console.php`

```php
// Add after other schedules

// {Backend} Import: Run daily at [TIME] (Europe/Copenhagen)
Schedule::command('{backend}:import')
    ->dailyAt('XX:XX')
    ->timezone('Europe/Copenhagen')
    ->withoutOverlapping()
    ->onOneServer();
```

**Checklist**:
- [ ] Add schedule entry
- [ ] Choose appropriate time (offset from other imports)
- [ ] Test: `php artisan schedule:list`

---

## 📁 Phase 5: Controllers

### 5.1 Create or Update Catalog Controller

**Reference**: `app/Http/Controllers/Pokemon/CatalogController.php`

Create new game-specific controller or update existing one to handle new backend.

```php
// app/Http/Controllers/{Game}/CatalogController.php

<?php

namespace App\Http\Controllers\{Game};

use App\Http\Controllers\Controller;
use App\Models\{Backend}\{Backend}Set;
use App\Models\{Backend}\{Backend}Card;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * List all sets
     */
    public function sets(Request $request)
    {
        $sets = {Backend}Set::orderBy('release_date', 'desc')
            ->paginate(24);

        return view('{game}.catalog.sets', [
            'sets' => $sets,
            'backend' => '{backend}',
        ]);
    }

    /**
     * Show set detail with cards
     */
    public function setDetail(string $setId)
    {
        $set = {Backend}Set::where('{backend}_id', $setId)->firstOrFail();
        
        $cards = {Backend}Card::where('set_{backend}_id', $set->id)
            ->orderBy('number')
            ->paginate(48);

        return view('{game}.catalog.set-cards', [
            'set' => $set,
            'cards' => $cards,
            'backend' => '{backend}',
        ]);
    }

    /**
     * Show card detail
     */
    public function cardDetail(string $cardId)
    {
        $card = {Backend}Card::where('{backend}_id', $cardId)->firstOrFail();

        return view('{game}.catalog.card', [
            'card' => $card,
            'backend' => '{backend}',
        ]);
    }
}
```

**Checklist**:
- [ ] Create CatalogController for new game
- [ ] Implement sets(), setDetail(), cardDetail() methods
- [ ] Add authorization/authentication if needed

---

### 5.2 Update Collection Controller

**Reference**: `app/Http/Controllers/CollectionController.php`

Add method to add cards from new backend:

```php
// app/Http/Controllers/CollectionController.php - ADD this method

/**
 * Add {Backend} card to collection
 */
public function add{Backend}(Request $request)
{
    $request->validate([
        '{backend}_card_id' => 'required|exists:{backend}_cards,id',
        'quantity' => 'nullable|integer|min:1|max:99',
        'condition' => 'nullable|in:mint,near_mint,excellent,good,light_played,played,poor',
        'language' => 'nullable|string|max:3',
        'is_foil' => 'nullable|boolean',
        'is_first_edition' => 'nullable|boolean',
    ]);

    // Check card limits based on subscription
    $user = Auth::user();
    $collectionCount = UserCollection::where('user_id', $user->id)->count();
    $cardLimit = $user->getCardLimit();

    if ($collectionCount >= $cardLimit) {
        return back()->with('error', __('messages.card_limit_reached', ['limit' => $cardLimit]));
    }

    // Add to collection
    UserCollection::create([
        'user_id' => $user->id,
        '{backend}_card_id' => $request->{backend}_card_id,
        'quantity' => $request->quantity ?? 1,
        'condition' => $request->condition ?? 'near_mint',
        'language' => $request->language ?? 'en',
        'is_foil' => $request->is_foil ?? false,
        'is_first_edition' => $request->is_first_edition ?? false,
    ]);

    return back()->with('success', __('messages.card_added_to_collection'));
}
```

**Checklist**:
- [ ] Add `add{Backend}()` method to CollectionController
- [ ] Update collection display logic to handle new backend
- [ ] Update statistics queries to include new backend cards

---

### 5.3 Update Deck Controller

**Reference**: `app/Http/Controllers/DeckController.php`

```php
// app/Http/Controllers/DeckController.php - ADD this method

/**
 * Add {Backend} card to deck
 */
public function addCard{Backend}(Request $request, Deck $deck)
{
    $this->authorize('update', $deck);

    $request->validate([
        '{backend}_card_id' => 'required|exists:{backend}_cards,id',
        'quantity' => 'nullable|integer|min:1|max:4',
    ]);

    // Check if card already in deck
    $existingCard = DeckCard::where('deck_id', $deck->id)
        ->where('{backend}_card_id', $request->{backend}_card_id)
        ->first();

    if ($existingCard) {
        $existingCard->increment('quantity', $request->quantity ?? 1);
    } else {
        DeckCard::create([
            'deck_id' => $deck->id,
            '{backend}_card_id' => $request->{backend}_card_id,
            'quantity' => $request->quantity ?? 1,
        ]);
    }

    return back()->with('success', __('messages.card_added_to_deck'));
}
```

**Checklist**:
- [ ] Add `addCard{Backend}()` method
- [ ] Update deck display logic
- [ ] Update deck statistics to include new backend

---

### 5.4 Update Global Search (CardSearchController)

**Reference**: `app/Http/Controllers/Api/CardSearchController.php`

The global search in the header needs to support your new backend. This controller provides typeahead suggestions across all card backends.

**⚠️ CRITICAL: Deduplication Strategy**

Each backend uses a **different unique identifier** for cards:
- **TCGDEX**: `tcgdex_id` (e.g., "base1-10") - includes set + card number
- **TCGCSV**: `product_id` (integer) - global unique ID
- **Your Backend**: Define appropriate unique key

**Why this matters**: The frontend JavaScript (`resources/js/cardSearch.js`) deduplicates search results to avoid showing the same card twice. Using the wrong key will cause:
- ❌ Missing results (cards with same number from different sets get deduplicated incorrectly)
- ❌ Duplicate results (non-unique keys allow duplicates through)

**Implementation Steps**:

1. **Add search method in CardSearchController** for your backend:

```php
// app/Http/Controllers/Api/CardSearchController.php

private function search{Backend}(string $query, int $limit, bool $collectionOnly): JsonResponse
{
    $escapedQuery = $this->escapeLikeWildcards($query);
    
    $results = {Backend}Card::query()
        ->select([
            '{backend}_cards.id as {backend}_card_id',
            '{backend}_cards.{backend}_id', // Your unique identifier
            '{backend}_cards.name',
            '{backend}_cards.card_number',
            '{backend}_sets.name as set_name',
            // ... other fields
        ])
        ->leftJoin('{backend}_sets', '{backend}_cards.set_{backend}_id', '=', '{backend}_sets.id')
        ->where(function($q) use ($escapedQuery) {
            $q->where('{backend}_cards.name', 'LIKE', "%{$escapedQuery}%")
              ->orWhere('{backend}_cards.card_number', 'LIKE', "%{$escapedQuery}%")
              ->orWhere('{backend}_cards.{backend}_id', 'LIKE', "%{$escapedQuery}%");
        })
        ->orderBy('{backend}_cards.id', 'DESC')
        ->limit($limit)
        ->get();

    // Format response - MUST include 'backend' field
    $formatted = $results->map(function ($card) {
        return [
            'backend' => '{backend}',  // ⚠️ REQUIRED for frontend routing
            '{backend}_card_id' => $card->{backend}_card_id,
            '{backend}_id' => $card->{backend}_id, // ⚠️ Your unique identifier
            'product_id' => null, // Not applicable
            'name' => $card->name,
            'card_number' => $card->card_number,
            'set_name' => $card->set_name,
            'set_code' => $card->set_code ?? null,
            'set_total' => $card->set_total ?? null,
            'image_url' => $card->image_url,
        ];
    });

    return response()->json($formatted);
}
```

2. **Update main index() method** to route to your backend:

```php
public function index(CardSearchRequest $request): JsonResponse
{
    $catalogBackend = $request->input('backend') ?: catalog_backend();
    
    if ($catalogBackend === '{backend}') {
        return $this->search{Backend}($query, $limit, $collectionOnly);
    }
    // ... existing backends
}
```

3. **Update frontend JavaScript** (`resources/js/cardSearch.js`):

```javascript
// Update deduplicateResults() function
function deduplicateResults(results) {
    const seen = new Set();
    return results.filter(card => {
        let key;
        if (card.backend === 'tcgdex') {
            key = card.tcgdex_id; // Unique across all sets
        } else if (card.backend === '{backend}') {
            key = card.{backend}_id; // ⚠️ Use YOUR unique identifier
        } else {
            key = card.product_id || `${card.group_id}-${card.card_number}`;
        }
        
        if (seen.has(key)) return false;
        seen.add(key);
        return true;
    });
}

// Update displayResults() to generate correct URLs
if (card.backend === '{backend}') {
    cardUrl = `/{game}/cards/${card.{backend}_id}`;
}
```

4. **Rebuild assets after editing JavaScript**:

```bash
npm run build
```

**Common Mistakes to Avoid**:

❌ **DON'T** use `card_number` as deduplication key (multiple sets have same numbers)
❌ **DON'T** forget to set `backend` field in API response (breaks frontend routing)
❌ **DON'T** use `product_id` for non-TCGCSV backends (it's null, causes incorrect deduplication)
❌ **DON'T** forget to rebuild assets after editing JavaScript

✅ **DO** use a globally unique identifier that includes set information
✅ **DO** test search with card numbers that appear in multiple sets (e.g., "001/102")
✅ **DO** verify all matching cards appear in results, not just the first one

**Testing Checklist**:
- [ ] Search for card name returns results from all sets
- [ ] Search for card number (e.g., "010/102") returns ALL matching cards, not just first
- [ ] Clicking result navigates to correct card detail page
- [ ] No duplicate cards in search results
- [ ] Search works for both authenticated and guest users

**Reference Implementation**:
- See commit fixing TCGDEX deduplication issue (Feb 2026)
- Compare dashboard search vs header search to verify consistency

---

### 5.5 Update Interaction Controllers

**Reference**: `app/Http/Controllers/Pokemon/CardInteractionController.php` or similar

```php
// Add methods to handle Like/Wishlist/Watch for new backend
// Pattern is same as TCGDEX implementation, just change column names
```

**Checklist**:
- [ ] Add like toggle method
- [ ] Add wishlist toggle method
- [ ] Add watch toggle method
- [ ] All methods should check `{backend}_card_id` column

---

## 📁 Phase 6: Routes

### 6.1 Add Web Routes

**File**: `routes/web.php`

```php
// {Game} Catalog Routes
Route::prefix('{game}')->name('{game}.')->group(function () {
    Route::get('/sets', [App\Http\Controllers\{Game}\CatalogController::class, 'sets'])->name('sets');
    Route::get('/sets/{set}', [App\Http\Controllers\{Game}\CatalogController::class, 'setDetail'])->name('sets.show');
    Route::get('/cards/{card}', [App\Http\Controllers\{Game}\CatalogController::class, 'cardDetail'])->name('cards.show');
    
    // Authenticated routes
    Route::middleware('auth')->group(function () {
        Route::post('/cards/{card}/like', [App\Http\Controllers\{Game}\CardInteractionController::class, 'toggleLike'])->name('cards.like');
        Route::post('/cards/{card}/wishlist', [App\Http\Controllers\{Game}\CardInteractionController::class, 'toggleWishlist'])->name('cards.wishlist');
        Route::post('/cards/{card}/watch', [App\Http\Controllers\{Game}\CardInteractionController::class, 'toggleWatch'])->name('cards.watch');
    });
});

// Collection routes
Route::post('/collection/add/{backend}', [App\Http\Controllers\CollectionController::class, 'add{Backend}'])->name('collection.add.{backend}');

// Deck routes
Route::post('/decks/{deck}/cards/{backend}', [App\Http\Controllers\DeckController::class, 'addCard{Backend}'])->name('decks.cards.add.{backend}');
```

**Checklist**:
- [ ] Add catalog routes
- [ ] Add interaction routes
- [ ] Add collection routes
- [ ] Add deck routes
- [ ] Test routes: `php artisan route:list | grep {game}`

---

## 📁 Phase 7: Views

### 7.1 Create Catalog Views

**Reference**: `resources/views/pokemon/catalog/`

Create directory structure:
```
resources/views/{game}/catalog/
├── sets.blade.php           (List all sets)
├── set-cards-{backend}.blade.php  (Set detail with cards)
└── card-{backend}.blade.php       (Card detail page)
```

**Key Elements to Include**:
- Set logo display
- Card grid with images
- Price display (gated by subscription)
- Like/Wishlist/Watch buttons (for authenticated users)
- Add to Collection button
- Add to Deck button with modal

**Reference Files**:
- `resources/views/pokemon/catalog/sets-tcgdex.blade.php`
- `resources/views/pokemon/catalog/set-cards-tcgdex.blade.php`
- `resources/views/pokemon/catalog/card-tcgdex.blade.php`

**⚠️ IMPORTANT - Sets List View Pattern**:

The sets list view **MUST** use:
1. **AJAX-based loading** instead of server-side pagination
2. **Search filter** with debounced input (300ms delay)
3. **"Load More" button** instead of classic pagination links
4. **Infinite scroll-like experience** (appending results, not replacing page)

**Why?**: Better UX, faster perceived performance, no page reloads, mobile-friendly.

**Implementation checklist for sets list**:
- [ ] Add search input field with debounced event listener
- [ ] Create AJAX endpoint in controller (e.g., `setsSearch()`)
- [ ] Return JSON with `data` array and `meta` (current_page, last_page, total)
- [ ] Render initial results via JavaScript on page load
- [ ] Show/hide "Load More" button based on `currentPage < lastPage`
- [ ] On "Load More" click: increment page, fetch, append results
- [ ] Handle empty states (no results, no more pages)
- [ ] Add loading spinner during AJAX requests

**Required Route**:
```php
// Example for Pokemon
Route::get('/{game}/sets/search', [CatalogController::class, 'setsSearch'])
    ->name('{game}.sets.search');
```

**Controller Method Example**:
```php
public function setsSearch(Request $request)
{
    $validated = $request->validate([
        'query' => 'nullable|string|max:100',
        'page' => 'integer|min:1',
    ]);

    $query = {Backend}Set::where('game_id', $currentGame->id);

    if (!empty($validated['query'])) {
        $query->where('name', 'like', "%{$validated['query']}%");
    }

    $sets = $query->orderByDesc('release_date')->paginate(24);

    return response()->json([
        'data' => $sets->map(fn($set) => [/* mapped fields */]),
        'meta' => [
            'current_page' => $sets->currentPage(),
            'last_page' => $sets->lastPage(),
            'per_page' => $sets->perPage(),
            'total' => $sets->total(),
        ],
    ]);
}
```

**Checklist**:
- [ ] Create views directory
- [ ] Create sets list view **with AJAX + Load More**
- [ ] Create sets search endpoint in controller
- [ ] Add route for sets search
- [ ] Create set detail view
- [ ] Create card detail view
- [ ] Add interaction buttons
- [ ] Test all views in browser

---

### 7.2 Register CMAPI Web Routes for New Games

When adding a new CMAPI-based game (e.g. `riftbound`), you must whitelist its slug in the CMAPI route group and in the sets search validation.

**File**: `routes/web.php`

```php
// CMAPI Browsing Routes (Lorcana, One Piece, Riftbound, ...)
Route::prefix('{game}')->whereIn('game', ['lorcana', 'onepiece', 'riftbound'])->group(function () {
    // Sets
    Route::get('/sets', [\App\Http\Controllers\CmapiSetController::class, 'index'])->name('cmapi.sets.index');
    Route::get('/sets/search', [\App\Http\Controllers\CmapiSetController::class, 'search'])->name('cmapi.sets.search');
    Route::get('/sets/{episode}', [\App\Http\Controllers\CmapiSetController::class, 'show'])->name('cmapi.sets.show');
    Route::get('/sets/{episode}/cards/search', [\App\Http\Controllers\CmapiSetController::class, 'cardsSearch'])->name('cmapi.sets.cards.search');

    // Cards
    Route::get('/cards/{cardId}', [\App\Http\Controllers\CmapiSetController::class, 'showCard'])->name('cmapi.cards.show');
});
```

**File**: `app/Http/Controllers/CmapiSetController.php`

```php
public function search(Request $request): JsonResponse
{
    $validated = $request->validate([
        'query' => 'nullable|string|max:100',
        'page' => 'integer|min:1',
        // Allow all supported CMAPI games; keep this in sync with routes/web.php
        'game' => 'required|in:lorcana,onepiece,riftbound',
    ]);

    $game = $validated['game'];
    $query = CmapiSet::where('game', $game)->withCount('cards');
    // ...
}
```

**Checklist**:
- [ ] Add new CMAPI game slug to the `whereIn('game', [...])` array
- [ ] Add the same slug to `CmapiSetController::search()` validation rule
- [ ] Verify `/{$slug}/sets` and `/{$slug}/sets/{episode}` load without 404
- [ ] Verify CMAPI dashboard widgets only show data for the selected game

---

### 7.3 Update Dashboard

**Reference**: `resources/views/dashboard.blade.php`

Update dashboard to show:
- Featured sets carousel for new game
- Missing cards widget (if applicable)
- Collection statistics for new game
- Recent additions

**Checklist**:
- [ ] Add game detection logic
- [ ] Create featured sets section
- [ ] Update statistics queries
- [ ] Test dashboard display when game is selected

---

### 7.4 Update Collection Views

**Reference**: `resources/views/collection/index.blade.php`

Update collection view to display cards from new backend.

**Key Changes**:
```blade
@if($catalogBackend === '{backend}')
    {{-- Display {Backend} cards --}}
    @foreach($items as $item)
        @if($item->{backend}_card_id)
            <div class="card">
                <img src="{{ $item->{backend}Card->image_small_url }}" alt="{{ $item->{backend}Card->name }}">
                <h3>{{ $item->{backend}Card->name }}</h3>
                {{-- More details --}}
            </div>
        @endif
    @endforeach
@endif
```

**Checklist**:
- [ ] Add backend detection in collection view
- [ ] Display cards from new backend
- [ ] Update remove card functionality
- [ ] Test collection display

---

### 7.4 Update Deck Views

**Reference**: `resources/views/decks/show.blade.php`

Similar to collection, update deck views to handle new backend.

**Checklist**:
- [ ] Add backend detection
- [ ] Display cards from new backend
- [ ] Update deck statistics
- [ ] Test deck display

---

### 7.5 Create Backend-Specific Deck Partials (RECOMMENDED)

**Reference**: `resources/views/decks/partials/card-grid-{tcgcsv,tcgdex,cmapi}.blade.php`

**Why?**: Instead of filling `show.blade.php` with nested if/else statements, create separate partial views for each backend. This improves:
- **Maintainability**: Each backend's logic is isolated
- **Readability**: No more "zigzag" conditionals
- **Scalability**: Easy to add new backends without touching existing code
- **Testing**: Each partial can be tested independently

**Pattern Applied in Feb 1, 2026 Refactoring**:
- Reduced `show.blade.php` from 1134 to 858 lines (-276 lines)
- Created 3 clean partials: TCGCSV (207 lines), TCGDEX (119 lines), CMAPI (111 lines)
- Each partial contains complete logic: card display, prices, interactions, forms

**Implementation Steps**:

1. **Create Partial Directory**:
```bash
mkdir -p resources/views/decks/partials
```

2. **Create Backend-Specific Partial**:
```blade
{{-- resources/views/decks/partials/card-grid-{backend}.blade.php --}}

{{-- Filter deck cards for this backend --}}
@php
    $backendCards = $deck->deckCards->filter(fn($dc) => $dc->{backend}_card_id !== null);
@endphp

@if($backendCards->count() > 0)
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach($backendCards as $deckCard)
    @php
        $card = $deckCard->{backend}Card;
        if (!$card) continue;
        
        // Backend-specific logic here
        $inCollection = auth()->user()->collection()
            ->where('{backend}_card_id', $card->id)
            ->exists();
        
        $displayImage = $card->image_url; // Adjust per backend
        $cardName = $card->name;
        $setName = $card->set_name;
        // ... more backend-specific extraction
    @endphp
    
    <div class="deck-card-item ...">
        {{-- Quantity Badge --}}
        <div class="absolute top-2 left-2 z-10 ...">
            x{{ $deckCard->quantity }}
        </div>
        
        {{-- Not in Collection Badge --}}
        @if(!$inCollection)
        <div class="absolute top-2 right-2 z-10">
            <form method="POST" action="{{ route('collection.add') }}" ...>
                @csrf
                <input type="hidden" name="{backend}_card_id" value="{{ $card->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" ...>+</button>
            </form>
        </div>
        @endif
        
        {{-- Card Image --}}
        <div class="aspect-[245/342] ...">
            <img src="{{ $displayImage }}" alt="{{ $cardName }}" ...>
        </div>
        
        {{-- Card Info --}}
        <div class="p-3">
            <h4>{{ $cardName }}</h4>
            <p>{{ $setName }}</p>
            
            {{-- Prices (if available for this backend) --}}
            @can('seePrices')
                {{-- Backend-specific price logic --}}
            @endcan
            
            {{-- Actions --}}
            <div class="flex gap-2 mt-3">
                {{-- Update Quantity --}}
                <form method="POST" action="{{ route('decks.cards.updateQuantity', [$deck, $deckCard]) }}" ...>
                    @csrf
                    @method('PATCH')
                    <input type="number" name="quantity" value="{{ $deckCard->quantity }}" ...>
                </form>
                
                {{-- Remove Button --}}
                <form method="POST" action="{{ route('decks.cards.remove', [$deck, $deckCard]) }}" ...>
                    @csrf
                    @method('DELETE')
                    <button type="submit" ...>Remove</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
```

3. **Update Main Deck View**:
```blade
{{-- resources/views/decks/show.blade.php --}}

@if($deck->deckCards->count() === 0)
    {{-- Empty state --}}
@else
    {{-- Card Grids by Backend --}}
    @include('decks.partials.card-grid-tcgcsv', ['deck' => $deck, 'preferredCurrency' => $preferredCurrency, 'defaultCurrency' => $defaultCurrency])
    @include('decks.partials.card-grid-tcgdex', ['deck' => $deck])
    @include('decks.partials.card-grid-cmapi', ['deck' => $deck])
    @include('decks.partials.card-grid-{backend}', ['deck' => $deck]) {{-- Add your new backend --}}
@endif
```

4. **Key Points**:
- Each partial filters `$deck->deckCards` for its backend only
- Use `@if($backendCards->count() > 0)` to avoid empty grids
- Pass necessary variables via `@include` second parameter
- Keep all backend-specific logic (prices, image URLs, name extraction) inside the partial
- Forms use correct backend column name (`{backend}_card_id`)

**Benefits Proven**:
- 24% reduction in main view size (276 lines removed)
- Zero if/else conditionals in main view
- Each backend fully independent
- Easy to add features to one backend without affecting others
- Better IDE support and syntax highlighting

**Checklist**:
- [ ] Create `resources/views/decks/partials/` directory
- [ ] Create `card-grid-{backend}.blade.php` partial
- [ ] Implement backend-specific filtering and logic
- [ ] Update main `show.blade.php` with `@include`
- [ ] Test deck display with cards from your backend
- [ ] Verify forms work (add to collection, update quantity, remove)
- [ ] Check prices display correctly (if applicable)

---

## 📁 Phase 8: Translations

### 8.1 Create Catalog Translations

**Reference**: `resources/lang/en/catalog.php`

Create translation files for each language:

---

### 8.2 Implement Keyboard Navigation for Search (RECOMMENDED)

**Reference**: `resources/js/quickAddCard.js` (dashboard search)

**Pattern**: Add arrow key navigation to typeahead search dropdowns for better UX.

**Implementation**:

```javascript
let highlightedIndex = -1;

// Arrow Down
if (e.key === 'ArrowDown') {
    e.preventDefault();
    highlightedIndex = Math.min(highlightedIndex + 1, results.length - 1);
    updateHighlight();
}

// Arrow Up
if (e.key === 'ArrowUp') {
    e.preventDefault();
    highlightedIndex = Math.max(highlightedIndex - 1, -1);
    updateHighlight();
}

// Enter
if (e.key === 'Enter' && highlightedIndex >= 0) {
    e.preventDefault();
    selectResult(results[highlightedIndex]);
}

// Escape
if (e.key === 'Escape') {
    closeDropdown();
    highlightedIndex = -1;
}

function updateHighlight() {
    document.querySelectorAll('.search-result').forEach((el, idx) => {
        el.classList.toggle('bg-white/20', idx === highlightedIndex);
        
        // Auto-scroll into view
        if (idx === highlightedIndex) {
            el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    });
}
```

**Key Features**:
- ArrowUp/Down navigate through results
- Visual highlight with background color change
- `scrollIntoView({ block: 'nearest' })` keeps highlighted item visible
- Enter selects highlighted result
- Escape closes dropdown and resets

**Where to Apply**:
- Header global search
- Dashboard "Quick Add Card" search
- Deck card search (collection + catalog)
- Collection filters typeahead

---

### 8.3 Card Number Format: Display "#10/102" Pattern

**Reference**: `app/Http/Controllers/Api/CardSearchController.php`

**Problem**: Card numbers stored as "10/102" in database, but users expect clean display "#10/102".

**Solution**: Extract `card_number` and `set_total` separately in API response.

**Backend Implementation** (TCGCSV example):

```php
// Controller method
public function searchTcgcsv(Request $request)
{
    $query = TcgcsvProduct::query()
        ->selectRaw('
            tcgcsv_products.*,
            SUBSTRING_INDEX(card_number, \'/\', 1) as card_number_only,
            SUBSTRING_INDEX(card_number, \'/\', -1) as set_total
        ');
    
    $cards = $query->get()->map(function ($card) {
        return [
            'backend' => 'tcgcsv',
            'product_id' => $card->product_id,
            'name' => $card->name,
            'card_number' => $card->card_number_only, // "10"
            'set_total' => $card->set_total,          // "102"
            'set_name' => $card->group->name ?? null,
            'image_url' => $card->image_url,
        ];
    });
    
    return response()->json($cards);
}
```

**For TCGDEX** (already separated):
```php
return [
    'backend' => 'tcgdex',
    'tcgdex_card_id' => $card->id,
    'name' => $card->name['en'] ?? $card->name,
    'card_number' => $card->local_id,           // Already separate
    'set_total' => $card->set->card_count_official, // Already separate
    'set_name' => $card->set->name['en'] ?? $card->set->name,
    'image_url' => $card->image_small_url,
];
```

**Frontend Display**:
```blade
{{-- In Blade templates --}}
<p class="text-gray-400 text-xs">
    {{ $setName }}
    @if($cardNumber && $setTotal)
        · #{{ $cardNumber }}/{{ $setTotal }}
    @elseif($cardNumber)
        · #{{ $cardNumber }}
    @endif
</p>
```

**JavaScript Rendering**:
```javascript
const cardInfo = `${setName} · #${cardNumber}/${setTotal}`;
```

**Benefits**:
- Cleaner display format
- Consistent across all searches
- Frontend doesn't need string parsing
- Backend handles complexity once
- Easy to conditionally show/hide set total

**Apply To**:
- All search endpoints (`/api/search/cards`)
- Collection views
- Deck views
- Catalog card grids

**Checklist**:
- [ ] Update search API to return separate `card_number` and `set_total`
- [ ] Use SQL functions (SUBSTRING_INDEX) for TCGCSV-style "10/102" format
- [ ] Update frontend to display "#10/102" consistently
- [ ] Test in: header search, dashboard search, deck search, collection

---

## 📁 Phase 9: Translations (continued)

### 9.1 Create Catalog Translations

**Reference**: `resources/lang/en/catalog.php`

Create translation files for each language:

```php
// resources/lang/en/{game}.php

<?php

return [
    // Sets
    'sets' => 'Sets',
    'set' => 'Set',
    'all_sets' => 'All Sets',
    'cards_in_set' => ':count cards',
    'released' => 'Released',
    
    // Cards
    'cards' => 'Cards',
    'card' => 'Card',
    'card_number' => 'Card #:number',
    'rarity' => 'Rarity',
    'artist' => 'Artist',
    
    // Prices
    'market_prices' => 'Market Prices',
    'eur_price' => 'EUR Price',
    'usd_price' => 'USD Price',
    
    // Actions
    'add_to_collection' => 'Add to Collection',
    'add_to_deck' => 'Add to Deck',
    'like' => 'Like',
    'wishlist' => 'Wishlist',
    'watch' => 'Watch',
    
    // Messages
    'no_cards_found' => 'No cards found',
    'loading' => 'Loading...',
];
```

**Checklist**:
- [ ] Create EN translations
- [ ] Create DA translations
- [ ] Create IT translations
- [ ] Test translations in views: `{{ __('game.sets') }}`

---

### 8.2 Update Common Translations

**Files**: `resources/lang/*/messages.php`, `dashboard.php`

Add game-specific messages:
- Card limit messages
- Success/error messages
- Dashboard strings

**Checklist**:
- [ ] Add messages to EN
- [ ] Add messages to DA
- [ ] Add messages to IT

---

## 📁 Phase 9: Testing & Verification

### 9.1 Database Testing

```bash
# Check tables exist
php artisan db:show

# Check data
php artisan tinker
>>> App\Models\{Backend}\{Backend}Set::count()
>>> App\Models\{Backend}\{Backend}Card::count()
```

**Checklist**:
- [ ] Tables created successfully
- [ ] Foreign keys working
- [ ] Indexes present

---

### 9.2 Import Testing

```bash
# Test import
php artisan {backend}:import --set=TESTSET

# Test full import
php artisan {backend}:import

# Test cards-only
php artisan {backend}:import --cards-only
```

**Checklist**:
- [ ] Single set import works
- [ ] Full import completes
- [ ] Cards-only import works
- [ ] Prices extracted correctly

---

### 9.3 Frontend Testing

**Manual Tests**:
1. Navigate to `/{game}/sets` - See sets list
2. Click on set - See cards grid
3. Click on card - See card detail
4. Click Like button - Verify state changes
5. Click Add to Collection - Verify card added
6. Check Dashboard - See stats update
7. Check Collection - See card displayed
8. Create deck - Add card to deck
9. Check pricing display (test with different subscription tiers)

**Checklist**:
- [ ] Sets list displays correctly
- [ ] Set detail shows cards
- [ ] Card detail page works
- [ ] Like/Wishlist/Watch toggle
- [ ] Add to collection works
- [ ] Add to deck works
- [ ] Dashboard shows correct data
- [ ] Prices displayed correctly
- [ ] Subscription gating works

---

## 📁 Phase 10: Documentation

### 10.1 Update PROJECT_STATUS.md

Add section for new game:

```markdown
### {Game} Integration (Date)
- ✅ {Backend} API client implemented
- ✅ Import system working
- ✅ Sets: X imported
- ✅ Cards: X imported
- ✅ Pricing integration: Cardmarket (EUR) + TCGPlayer (USD)
- ✅ Collection management
- ✅ Deck building
- ✅ User interactions (Like/Wishlist/Watch)
```

**Checklist**:
- [ ] Update PROJECT_STATUS.md
- [ ] Document API source
- [ ] Document pricing sources
- [ ] Document known limitations

---

### 10.2 Update OPERATIONS.md

Add import commands:

```markdown
### Import {Game}

**Import all:**
```bash
php artisan {backend}:import
```

**Import single set:**
```bash
php artisan {backend}:import --set=SETCODE
```

**Cards only:**
```bash
php artisan {backend}:import --cards-only
```
```

**Checklist**:
- [ ] Add to OPERATIONS.md
- [ ] Document command options
- [ ] Add to ETL schedule section

---

## 🎉 Phase 11: Deployment

### 11.1 Pre-Deploy Checklist

- [ ] All migrations run successfully
- [ ] All tests pass
- [ ] Import tested on staging
- [ ] Frontend tested on staging
- [ ] Translations complete
- [ ] Config values set in production .env
- [ ] API keys configured
- [ ] **Deck sharing tested with all card types**

---

### 11.2 Deck Sharing Multi-Backend Support

**CRITICAL**: Shared deck view must support all backends!

**File**: `app/Http/Controllers/DeckController.php` → `publicView()`

```php
public function publicView(string $token): View
{
    $deck = Deck::where('shared_token', $token)
        ->where('is_shared', true)
        ->with([
            'deckCards.card.group',         // TCGCSV (MTG/YGO)
            'deckCards.tcgdexCard.set',     // TCGDEX (Pokemon)
            'deckCards.cmapiCard.set',      // CMAPI (Lorcana/One Piece)
            'deckCards.{yourBackend}Card.set', // ← ADD YOUR BACKEND HERE
            'deckCards.photos',             // User photos
            'game',
            'user'
        ])
        ->firstOrFail();
}
```

**File**: `resources/views/decks/public.blade.php`

Handle multilingual fields (if your backend uses JSON arrays):

```php
@php
    // Handle multilingual fields (arrays)
    $cardName = $card->name;
    if (is_array($cardName)) {
        $cardName = $cardName['en'] ?? $cardName['da'] ?? 'Unknown';
    }
    
    // Handle image formats
    if (isset($card->image_small)) {
        // TCGCSV format
        $cardImageSmall = $card->image_small;
    } elseif (isset($card->image_small_url)) {
        // TCGDEX format
        $cardImageSmall = $card->getLowQualityImageUrl();
    } elseif (isset($card->images) && is_array($card->images)) {
        // CMAPI format
        $cardImageSmall = $card->images['small'] ?? null;
    }
    // ← ADD YOUR BACKEND IMAGE LOGIC HERE
@endphp
```

**Testing Checklist**:
- [ ] Create deck with cards from your game
- [ ] Share deck (click "Share Deck" button)
- [ ] Open shared link in incognito/private window
- [ ] Verify card images display correctly
- [ ] Verify card names are strings (not arrays)
- [ ] Verify set names display correctly
- [ ] Verify user-uploaded photos display (if any)
- [ ] Test hover preview on card images
- [ ] Check no PHP errors in logs

**Common Issues**:
- ❌ `htmlspecialchars(): Argument #1 must be string, array given`
  - **Fix**: Extract string from multilingual array fields
- ❌ Images not showing
  - **Fix**: Add image URL logic in `@php` block
- ❌ Set names missing
  - **Fix**: Add `.set` to eager loading in controller

---

### 11.3 Deploy Steps

```bash
# On server
cd /path/to/project
git pull origin main
composer install --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run import
php artisan {backend}:import
```

**Checklist**:
- [ ] Code deployed
- [ ] Migrations run
- [ ] Caches cleared
- [ ] Import completed
- [ ] Site tested in production
- [ ] **Shared deck link tested**

---

### 11.4 Scheduler Update

Verify scheduler includes new import:

```bash
php artisan schedule:list
```

**Checklist**:
- [ ] New import command scheduled
- [ ] Timing doesn't conflict with other imports
- [ ] Cron job running

---

## 📚 Reference Implementations

### Key Files to Reference

**Pokemon/TCGDEX Implementation** (completed Jan 28-31, 2026):

| Component | File Path |
|-----------|-----------|
| Models | `app/Models/Tcgdx/TcgdxCard.php`, `TcgdxSet.php` |
| API Client | `app/Services/Tcgdx/TcgdxClient.php` |
| Import Service | `app/Services/Tcgdx/TcgdxImportService.php` |
| Command | `app/Console/Commands/TcgdxImportCommand.php` |
| Controller | `app/Http/Controllers/Pokemon/CatalogController.php` |
| Views | `resources/views/pokemon/catalog/*` |
| Migrations | `database/migrations/*tcgdx*.php` |
| Translations | `resources/lang/*/catalog.php` |

---

## 🚨 Common Pitfalls

1. **Forgotten Placeholders**: Always search/replace ALL `{Backend}`, `{backend}`, `{Game}`, `{game}` placeholders
2. **Foreign Key Order**: Create parent tables (sets) before child tables (cards)
3. **Model Namespaces**: Use full namespace in relationships: `App\Models\{Backend}\{Backend}Card`
4. **Nullable Columns**: All `*_card_id` columns in user tables must be nullable
5. **Price Extraction**: Verify API response structure before implementing price extraction
6. **Image URLs**: Test image URLs work before importing all cards
7. **Set ID Passing**: Pass `$set->id` (database ID) not `$set->{backend}_id` (API ID) to normalizeCard
8. **Backend Detection**: Update `games.catalog_backend` column in database, not hardcode
9. **Translation Keys**: Don't hardcode any UI text, use `__()` helper
10. **Subscription Gating**: Remember to gate pricing display based on user tier

---

## ✅ Final Checklist

Before considering implementation complete:

- [ ] Database schema complete (sets, cards, import_runs, user table extensions)
- [ ] Models created with proper relationships
- [ ] API client working (tested in tinker)
- [ ] Import service functional (2-phase import)
- [ ] Import command created and scheduled
- [ ] Controllers handle all CRUD operations
- [ ] Routes registered and tested
- [ ] Views created and styled
- [ ] Translations complete (EN/DA/IT)
- [ ] Collection integration works
- [ ] Deck integration works
- [ ] Dashboard updated
- [ ] User interactions (Like/Wishlist/Watch) functional
- [ ] Pricing display correct
- [ ] Subscription gating working
- [ ] Documentation updated
- [ ] Tested on staging
- [ ] Deployed to production
- [ ] Production import completed successfully

---

## 📞 Support

When encountering issues, check:
1. **Error logs**: `storage/logs/laravel.log`
2. **Database**: Verify foreign keys and indexes
3. **API responses**: Test API client in tinker
4. **Model relationships**: `php artisan tinker` → test relationships
5. **Routes**: `php artisan route:list`
6. **Translations**: Verify keys exist in all language files

---

## 💰 Phase 12: Currency Conversion & Price Visibility

### Overview

Basecard has a subscription-based pricing model with different price visibility rules:
- **Free**: Can see individual card prices in original currency (EUR/USD)
- **Advanced/Premium**: See prices converted to their preferred currency

### 12.1 Prerequisites

**Existing Infrastructure**:
- `User.preferred_currency` column (EUR, USD, GBP, DKK, etc.)
- `App\Services\CurrencyService` with conversion rates
- `User::isAdvanced()` and `User::isPremium()` helper methods

**Checklist**:
- [ ] Verify CurrencyService exists with latest exchange rates
- [ ] Test User tier detection methods work

---

### 12.2 Create Price Display Partial

**File**: `resources/views/{backend}/cards/partials/prices.blade.php`

```php
{{--
    Card Prices Partial for {BACKEND}
    
    Props:
    - $card: Card model instance with price_eur/price_usd
    - $size: 'large'|'small' (optional, default 'large')
--}}

@php
    $user = auth()->user();
    $canSeePrices = $user && ($user->isAdvanced() || $user->isPremium());
    $preferredCurrency = $canSeePrices && $user ? ($user->preferred_currency ?? 'EUR') : 'EUR';
    $needsConversion = $preferredCurrency && $preferredCurrency !== 'EUR';
    $size = $size ?? 'large';
    $isLarge = $size === 'large';
@endphp

@if($card->price_eur || $card->price_usd)
    <div class="space-y-3">
        {{-- EUR Price (CardMarket) --}}
        @if($card->price_eur)
        <div class="flex justify-between items-center py-3 border-b border-white/10">
            <span class="{{ $isLarge ? 'text-sm' : 'text-xs' }} font-medium text-gray-400">
                CardMarket
            </span>
            @if($canSeePrices && $needsConversion)
                @php
                    $convertedPrice = \App\Services\CurrencyService::convert(
                        $card->price_eur, 
                        'EUR', 
                        $preferredCurrency
                    );
                    $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                @endphp
                <div class="text-right">
                    <div class="{{ $isLarge ? 'text-2xl' : 'text-lg' }} font-bold text-green-400">
                        {{ $symbol }}{{ number_format($convertedPrice, 2) }}
                    </div>
                    <div class="text-xs text-gray-500">
                        (€{{ number_format($card->price_eur, 2) }})
                    </div>
                </div>
            @else
                <span class="{{ $isLarge ? 'text-2xl' : 'text-lg' }} font-bold text-green-400">
                    €{{ number_format($card->price_eur, 2) }}
                </span>
            @endif
        </div>
        @endif
        
        {{-- USD Price (TCGPlayer) --}}
        @if($card->price_usd)
        <div class="flex justify-between items-center py-3 border-b border-white/10">
            <span class="{{ $isLarge ? 'text-sm' : 'text-xs' }} font-medium text-gray-400">
                TCGPlayer
            </span>
            @if($canSeePrices && $needsConversion && $preferredCurrency !== 'USD')
                @php
                    $convertedPrice = \App\Services\CurrencyService::convert(
                        $card->price_usd, 
                        'USD', 
                        $preferredCurrency
                    );
                    $symbol = \App\Services\CurrencyService::getSymbol($preferredCurrency);
                @endphp
                <div class="text-right">
                    <div class="{{ $isLarge ? 'text-2xl' : 'text-lg' }} font-bold text-blue-400">
                        {{ $symbol }}{{ number_format($convertedPrice, 2) }}
                    </div>
                    <div class="text-xs text-gray-500">
                        (${{ number_format($card->price_usd, 2) }})
                    </div>
                </div>
            @else
                <span class="{{ $isLarge ? 'text-2xl' : 'text-lg' }} font-bold text-blue-400">
                    ${{ number_format($card->price_usd, 2) }}
                </span>
            @endif
        </div>
        @endif
        
        <div class="text-xs text-gray-500 mt-4">
            Prices from CardMarket API
            @if($canSeePrices && $needsConversion)
                <br>Converted to {{ $preferredCurrency }} (original price shown below)
            @endif
        </div>
    </div>
@else
    <div class="text-sm text-gray-400 text-center py-4">
        No price data available
    </div>
@endif
```

**Checklist**:
- [ ] Create partial file
- [ ] Test with Free user (sees only EUR/USD)
- [ ] Test with Advanced user with preferred_currency = DKK (sees converted price)

---

### 12.3 Update Card Detail View

**File**: `resources/views/{backend}/cards/show.blade.php`

Replace inline price display with partial:

```php
<!-- Pricing Information -->
<div class="bg-[#161615] border border-white/15 rounded-2xl shadow-xl p-6">
    <h2 class="text-xl font-bold text-white mb-4">Market Prices</h2>
    @include('{backend}.cards.partials.prices', ['card' => $card, 'size' => 'large'])
</div>
```

**Checklist**:
- [ ] Replace inline price code with include
- [ ] Verify page still renders correctly
- [ ] Test with both user types

---

### 12.4 Add Price Display in Card Grid (Sets List)

**File**: `resources/views/{backend}/sets/show.blade.php`

Add JavaScript currency conversion for AJAX-loaded cards:

```javascript
<script>
// User preferences for price display
@php
    $user = auth()->user();
    $canSeePrices = $user && ($user->isAdvanced() || $user->isPremium());
    $preferredCurrency = $canSeePrices && $user ? ($user->preferred_currency ?? 'EUR') : 'EUR';
@endphp
const userCanSeePrices = {{ $canSeePrices ? 'true' : 'false' }};
const preferredCurrency = '{{ $preferredCurrency }}';

// Exchange rates (mirror of CurrencyService)
const exchangeRates = {
    'EUR': 1.0,
    'USD': 1.05,
    'GBP': 0.85,
    'DKK': 7.46,
    'SEK': 11.20,
    'NOK': 11.50,
    'CHF': 0.95,
    'JPY': 155.0,
    'CAD': 1.45,
    'AUD': 1.65,
};

const currencySymbols = {
    'EUR': '€',
    'USD': '$',
    'GBP': '£',
    'DKK': 'kr',
    'SEK': 'kr',
    'NOK': 'kr',
    'CHF': 'CHF',
    'JPY': '¥',
    'CAD': 'C$',
    'AUD': 'A$',
};

function convertPrice(amount, from, to) {
    if (from === to) return amount;
    const amountInEur = amount / exchangeRates[from];
    return amountInEur * exchangeRates[to];
}

function formatPrice(amount, currency) {
    const symbol = currencySymbols[currency] || currency;
    const formatted = amount.toFixed(2);
    
    // Symbol before for most currencies
    if (['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'].includes(currency)) {
        return `${symbol}${formatted}`;
    }
    // Symbol after for Nordic currencies
    return `${formatted} ${symbol}`;
}

function createCardElement(card) {
    // ... existing image/badge code ...
    
    // Price display (only for Advanced/Premium users)
    let priceHtml = '';
    if (userCanSeePrices && card.price_eur) {
        let displayPrice = card.price_eur;
        let displayCurrency = 'EUR';
        
        if (preferredCurrency !== 'EUR') {
            displayPrice = convertPrice(card.price_eur, 'EUR', preferredCurrency);
            displayCurrency = preferredCurrency;
        }
        
        priceHtml = `
            <div class="text-xs font-semibold text-green-400 mt-1">
                ${formatPrice(displayPrice, displayCurrency)}
            </div>
        `;
    }
    
    return `
        <a href="/{game}/cards/${card.id}" class="block group">
            <div class="bg-black/50 border border-white/20 rounded-lg overflow-hidden hover:border-blue-400 transition shadow-lg relative">
                <!-- Card image, name, etc -->
                <div class="p-2">
                    <div class="text-sm font-semibold text-white truncate">
                        ${card.name}
                    </div>
                    ${priceHtml}
                </div>
            </div>
        </a>
    `;
}
</script>
```

**Checklist**:
- [ ] Add user preferences to JavaScript
- [ ] Implement price conversion functions
- [ ] Update card element creation to include price
- [ ] Verify Free users don't see prices in grid
- [ ] Verify Advanced/Premium users see converted prices

---

### 12.5 Testing Scenarios

**Test Case 1: Free User**
```bash
# Set user to Free tier
php artisan tinker
>>> $user = User::find(1);
>>> $user->organization_id = null; // Free tier
>>> $user->save();
```

Expected:
- Card detail page: Shows EUR price as-is (€0.03)
- Card grid: No prices displayed
- No conversion happens

**Test Case 2: Advanced User with DKK**
```bash
php artisan tinker
>>> $user = User::find(1);
>>> $org = Organization::first();
>>> $org->pricing_plan_id = PricingPlan::where('code', 'advanced')->first()->id;
>>> $org->save();
>>> $user->organization_id = $org->id;
>>> $user->preferred_currency = 'DKK';
>>> $user->save();
```

Expected:
- Card detail page: Shows "0.22 kr (€0.03)"
- Card grid: Shows "0.22 kr" on each card
- Conversion: 0.03 EUR × 7.46 = 0.22 DKK

**Test Case 3: Premium User with USD**
```bash
php artisan tinker
>>> $user = User::find(1);
>>> $user->preferred_currency = 'USD';
>>> $user->save();
```

Expected:
- Card detail page: Shows "$0.03 (€0.03)" [almost same due to 1.05 rate]
- Card grid: Shows "$0.03" on each card

---

### 12.6 Checklist

- [ ] Create price partial for card detail
- [ ] Update card detail view to use partial
- [ ] Add JavaScript conversion for card grids
- [ ] Test with Free user (no conversion, grid has no prices)
- [ ] Test with Advanced user + DKK (sees converted prices)
- [ ] Test with Premium user + USD (sees USD conversion)
- [ ] Verify original price shown in parentheses
- [ ] Check performance (conversions are cheap calculations)

---

## 🎴 Appendix A: Lorcana Implementation (CardMarket API)

### Quick Start for Lorcana

**Backend name**: `cmapi` (CardMarket API)  
**Game slug**: `lorcana`  
**API**: https://rapidapi.com/tcggopro/api/cardmarket-api-tcg

### Key Differences from TCGDEX:

1. **API Terminology**:
   - Sets = "Episodes" (endpoint: `/lorcana/episodes`)
   - Cards endpoint: `/lorcana/episodes/{id}/cards`

2. **Pricing Structure**:
   ```json
   {
     "prices": {
       "cardmarket": {
         "currency": "EUR",
         "lowest_near_mint": 750,  // In CENTS
         "30d_average": 192.79,
         "graded": {
           "psa": { "psa10": 279 }
         }
       },
       "tcg_player": {
         "currency": "USD",
         "market_price": 146.69  // In CENTS
       }
     }
   }
   ```
   **Important**: Prices are in cents, divide by 100!

3. **Additional Migration Columns for Lorcana**:
   ```php
   // Add to cmapi_cards migration
   $table->integer('ink_cost')->nullable();
   $table->string('card_type')->nullable(); // Character, Action, Item, Location
   $table->integer('lore_value')->nullable();
   $table->string('ink_color')->nullable(); // Amber, Amethyst, Emerald, Ruby, Sapphire, Steel
   ```

4. **Rate Limits** (Plan accordingly):
   - Free: 100 req/day, 30 req/min → For testing only
   - Pro ($9.90/mo): 3,000 req/day → Good for daily imports
   - Ultra ($24.90/mo): 15,000 req/day → Full initial import

5. **Sample Commands**:
   ```bash
   # Initial import
   php artisan cmapi:import --game=lorcana
   
   # Import single set
   php artisan cmapi:import --game=lorcana --episode=1
   
   # Cards only (after sets exist)
   php artisan cmapi:import --game=lorcana --cards-only
   ```

### Reusable for One Piece

The same `CmapiClient` can handle One Piece:
```php
// Just change game parameter
$client = new CmapiClient('onepiece');
```

### Example Integration Code

```php
// config/cmapi.php - Already created above

// app/Services/Cmapi/CmapiClient.php - See template in Phase 3

// app/Console/Commands/CmapiImportCommand.php
protected $signature = 'cmapi:import 
                        {--game=lorcana : Game to import (lorcana, onepiece)}
                        {--episode= : Import single episode/set}
                        {--cards-only : Import only cards}
                        {--fresh : Truncate tables}';
```

---

## 🏪 Appendix B: CardMarket Price Historicization

### IMPORTANT: CardMarket API vs S3 Data

**⚠️ CRITICAL NOTE**: The CardMarket API mentioned in this guide (via RapidAPI) **DOES NOT EXIST** as a real API. It's actually S3-hosted static JSON files.

### What Actually Works

CardMarket provides public S3 buckets with daily price data:

**Products Catalog**:
```
https://downloads.s3.cardmarket.com/productCatalog/productList/products_singles_{GAME_ID}.json
```

**Price Guide**:
```
https://downloads.s3.cardmarket.com/productCatalog/priceGuide/price_guide_{GAME_ID}.json
```

**Game IDs**:
- Lorcana: `19`
- One Piece: `26`
- Pokemon: `6`
- Magic: `1`
- Yu-Gi-Oh: `3`

### Data Format (JSON, not CSV!)

**Products File Structure**:
```json
{
  "version": 1,
  "createdAt": "2026-01-31T09:21:45+0100",
  "products": [
    {
      "idProduct": 726997,
      "name": "Mickey Mouse - Brave Little Tailor",
      "idCategory": 1629,
      "categoryName": "Lorcana Single",
      "idExpansion": 5435,
      "idMetacard": 422798,
      "dateAdded": "2023-08-09 17:02:49"
    }
  ]
}
```

**Price Guide Structure**:
```json
{
  "version": 1,
  "createdAt": "2026-01-31T02:44:21+0100",
  "priceGuides": [
    {
      "idProduct": 726997,
      "1": {"MINT": 12.50, "EXC": 9.00, "POOR": 3.50},
      "2": {"MINT": 13.00, "EXC": 9.50, "POOR": 4.00}
    }
  ]
}
```

**Language Codes** (numeric keys in price guide):
- `1` = English
- `2` = French
- `3` = German
- `4` = Spanish
- `5` = Italian

### Implementation: Separate Staging Tables

For price historicization, you need **separate staging tables**:

**Migration**:
```php
// 1. Staging products (downloaded from S3)
Schema::create('staging_cmapi_products', function (Blueprint $table) {
    $table->id();
    $table->string('cardmarket_id')->index();
    $table->string('game')->index(); // lorcana, onepiece
    $table->string('name');
    $table->string('set_name')->nullable();
    $table->string('number')->nullable();
    $table->json('raw_data');
    $table->timestamp('fetched_at');
    $table->string('status')->default('pending'); // pending, matched, error
    $table->timestamps();
});

// 2. Staging prices (multi-language)
Schema::create('staging_cmapi_prices', function (Blueprint $table) {
    $table->id();
    $table->string('cardmarket_id')->index();
    $table->string('language', 5)->index(); // en, fr, de, es, it
    $table->string('condition', 10)->index(); // NM, EXC, GD, LP, PL, POOR
    $table->decimal('price_eur', 10, 2);
    $table->timestamp('price_date')->index();
    $table->timestamps();
});

// 3. Production price history (after validation)
Schema::create('cmapi_price_history', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('cmapi_card_id')->index();
    $table->string('language', 5)->index();
    $table->string('condition', 10)->index();
    $table->decimal('price_eur', 10, 2);
    $table->decimal('price_trend_eur', 10, 2)->nullable();
    $table->date('price_date')->index();
    $table->timestamps();
    
    $table->foreign('cmapi_card_id')
          ->references('id')
          ->on('cmapi_cards')
          ->onDelete('cascade');
    
    $table->unique(['cmapi_card_id', 'language', 'condition', 'price_date']);
});
```

### Workflow

1. **Download** → S3 JSON files (products + prices)
2. **Stage** → Insert to `staging_cmapi_*` tables
3. **Validate** → Match products to existing `cmapi_cards` by:
   - Primary: `cardmarket_id`
   - Fallback: `set_name` + `number`
4. **Promote** → Create `cmapi_price_history` records
5. **Clean** → Delete staging data >7 days old

### Service Template

```php
// app/Services/Cmapi/CardMarketPriceSyncService.php

public function importFromS3(string $game): array
{
    $gameId = ['lorcana' => 19, 'onepiece' => 26][$game];
    
    // Step 1: Download products JSON
    $productsUrl = "https://downloads.s3.cardmarket.com/productCatalog/productList/products_singles_{$gameId}.json";
    $productsData = Http::get($productsUrl)->json();
    $this->importProducts($game, $productsData['products']); // Extract 'products' array!
    
    // Step 2: Download prices JSON
    $pricesUrl = "https://downloads.s3.cardmarket.com/productCatalog/priceGuide/price_guide_{$gameId}.json";
    $pricesData = Http::get($pricesUrl)->json();
    $this->importPrices($game, $pricesData['priceGuides']); // Extract 'priceGuides' array!
}

protected function importProducts(string $game, array $products): int
{
    foreach ($products as $product) {
        if (!is_array($product)) continue; // Skip metadata
        
        DB::table('staging_cmapi_products')->insert([
            'cardmarket_id' => $product['idProduct'],
            'game' => $game,
            'name' => $product['name'],
            'set_name' => $product['categoryName'] ?? null,
            'raw_data' => json_encode($product),
            'fetched_at' => now(),
        ]);
    }
}

protected function importPrices(string $game, array $priceGuides): int
{
    $languageMap = [1 => 'en', 2 => 'fr', 3 => 'de', 4 => 'es', 5 => 'it'];
    
    foreach ($priceGuides as $guide) {
        if (!is_array($guide)) continue;
        
        foreach ($guide as $langId => $prices) {
            if (!is_numeric($langId) || !isset($languageMap[$langId])) continue;
            
            foreach ($prices as $condition => $price) {
                DB::table('staging_cmapi_prices')->insert([
                    'cardmarket_id' => $guide['idProduct'],
                    'language' => $languageMap[$langId],
                    'condition' => strtoupper($condition),
                    'price_eur' => $price,
                    'price_date' => now(),
                ]);
            }
        }
    }
}
```

### Important Notes

1. **Ask for S3 Links First**: Before implementing, verify the game has public S3 files
2. **JSON Structure**: Always extract nested arrays (`products`, `priceGuides`)
3. **No API Authentication**: S3 files are public, no keys needed
4. **Update Frequency**: Files update daily around 2 AM CET
5. **Staging Area Required**: Don't write directly to production tables

### Daily Sync Script

```bash
#!/bin/bash
# Daily CardMarket price sync

php artisan cardmarket:sync-prices --game=lorcana  # Download to staging
php artisan cardmarket:sync-prices --game=lorcana --promote  # Staging → Production
php artisan cardmarket:sync-prices --game=lorcana --clean  # Remove old staging
```

For complete implementation, see:
- `CARDMARKET_PRICE_SYNC_GUIDE.md`
- `app/Services/Cmapi/CardMarketPriceSyncService.php`
- `database/migrations/*_create_cardmarket_staging_and_history_tables.php`

---

**End of Guide**

This checklist ensures a systematic, non-zigzag implementation of new games following the proven TCGDEX pattern. Work through phases sequentially and check off items as completed.
