# 🎮 New Game Implementation Guide

**Reference Document for Adding MTG / YGO / Lorcana**

*Created: January 31, 2026*  
*Based on: Pokemon/TCGDEX implementation (Jan 28-31, 2026)*

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
1. **Game to implement**: [ ] MTG  [ ] YGO  [ ] Lorcana
2. **API Source**: 
   - MTG: Scryfall API (https://scryfall.com/docs/api)
   - YGO: YGOPRODeck API (https://ygoprodeck.com/api-guide/)
   - Lorcana: TBD
3. **Backend code name**: `scryfall` / `ygoprodeck` / `lorcana`

---

## 📁 Phase 1: Database Setup

### 1.1 Create Game Entry
**File**: Database seeder or manual SQL  
**Table**: `games`

```sql
-- Example for MTG
INSERT INTO games (id, name, slug, tcgcsv_category_id, catalog_backend, created_at, updated_at)
VALUES (2, 'Magic: The Gathering', 'magic', 1, 'scryfall', NOW(), NOW());
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

**Add to .env**:
```env
{BACKEND_UPPERCASE}_BASE_URL=https://api.example.com
{BACKEND_UPPERCASE}_API_KEY=your_key_here
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

### 5.4 Update Interaction Controllers

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

**Checklist**:
- [ ] Create views directory
- [ ] Create sets list view
- [ ] Create set detail view
- [ ] Create card detail view
- [ ] Add interaction buttons
- [ ] Test all views in browser

---

### 7.2 Update Dashboard

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

### 7.3 Update Collection Views

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

## 📁 Phase 8: Translations

### 8.1 Create Catalog Translations

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

---

### 11.2 Deploy Steps

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

---

### 11.3 Scheduler Update

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

**End of Guide**

This checklist ensures a systematic, non-zigzag implementation of new games following the proven TCGDEX pattern. Work through phases sequentially and check off items as completed.
