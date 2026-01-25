<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TcgcsvProduct extends Model
{
    protected $table = 'tcgcsv_products';
    protected $primaryKey = 'product_id';
    public $incrementing = true;
    
    protected $fillable = [
        'category_id',
        'game_id',
        'group_id',
        'product_id',
        'language',
        'name',
        'clean_name',
        'image_url',
        'rarity',
        'card_number',
        'modified_on',
        'extended_data',
        'raw',
        'visible_lookup_key',
    ];
    
    protected $casts = [
        'category_id' => 'integer',
        'group_id' => 'integer',
        'product_id' => 'integer',
        'modified_on' => 'datetime',
        'extended_data' => 'array',
        'raw' => 'array',
    ];
    
    public function group(): BelongsTo
    {
        return $this->belongsTo(TcgcsvGroup::class, 'group_id', 'group_id');
    }
    
    public function prices(): HasMany
    {
        return $this->hasMany(TcgcsvPrice::class, 'product_id', 'product_id');
    }
    
    /**
     * Get the RapidAPI card data directly via rapidapi_card_id
     */
    public function rapidapiCard()
    {
        return $this->belongsTo(\App\Models\RapidapiCard::class, 'rapidapi_card_id', 'id');
    }
    
    /**
     * Get the Cardmarket product directly via cardmarket_product_id
     */
    public function cardmarketProduct()
    {
        return $this->belongsTo(\App\Models\CardmarketProduct::class, 'cardmarket_product_id', 'cardmarket_product_id');
    }
    
    /**
     * Get all Cardmarket product variants (same metacard, different versions)
     */
    public function cardmarketVariants()
    {
        return $this->hasMany(\App\Models\CardmarketProduct::class, 'id_metacard', 'cardmarket_product_id')
            ->where('cardmarket_product_id', '!=', $this->cardmarket_product_id);
    }
    
    /**
     * Get the TCGdex card data
     */
    public function tcgdxCard()
    {
        return $this->belongsTo(\App\Models\Tcgdx\TcgdxCard::class, 'tcgdex_card_id', 'tcgdex_id');
    }

    /**
     * Check if this product has Cardmarket variants
     */
    public function hasCardmarketVariants(): bool
    {
        if (!$this->cardmarket_product_id) {
            return false;
        }
        
        return \App\Models\CardmarketProduct::where('id_metacard', function($query) {
            $query->select('id_metacard')
                  ->from('cardmarket_products')
                  ->where('cardmarket_product_id', $this->cardmarket_product_id)
                  ->limit(1);
        })->where('cardmarket_product_id', '!=', $this->cardmarket_product_id)
          ->exists();
    }
    
    /**
     * Get Cardmarket variants grouped by type
     * Extracts type from name (Normal, Reverse, 1st Edition, etc.)
     */
    public function getCardmarketVariantsByType()
    {
        return $this->cardmarketVariants()
            ->with('latestPriceQuote')
            ->get()
            ->groupBy(function ($variant) {
                $name = strtolower($variant->name ?? '');
                
                if (str_contains($name, '1st edition') || str_contains($name, '1. edition')) {
                    return '1st Edition';
                }
                if (str_contains($name, 'reverse') || str_contains($name, 'holo')) {
                    return 'Reverse Holo';
                }
                if (str_contains($name, 'promo')) {
                    return 'Promo';
                }
                if (str_contains($name, 'unlimited')) {
                    return 'Unlimited';
                }
                
                return 'Normal';
            });
    }
    
    /**
     * Get price range across all Cardmarket variants
     * Returns array with min, max, and average prices
     */
    public function getCardmarketPriceRange(): array
    {
        $variants = $this->cardmarketVariants()
            ->with('latestPriceQuote')
            ->get();
        
        if ($variants->isEmpty()) {
            return [
                'min' => null,
                'max' => null,
                'avg' => null,
            ];
        }
        
        $prices = $variants
            ->filter(fn($variant) => $variant->latestPriceQuote !== null)
            ->map(fn($variant) => $variant->latestPriceQuote->avg ?? 0)
            ->filter(fn($price) => $price > 0);
        
        if ($prices->isEmpty()) {
            return [
                'min' => null,
                'max' => null,
                'avg' => null,
            ];
        }
        
        return [
            'min' => $prices->min(),
            'max' => $prices->max(),
            'avg' => round($prices->avg(), 2),
        ];
    }
    
    /**
     * Get the default Cardmarket variant to display
     * Prioritizes: Normal > Unlimited > first available
     */
    public function getDefaultCardmarketVariant()
    {
        $variants = $this->cardmarketVariants()
            ->with('latestPriceQuote')
            ->get();
        
        if ($variants->isEmpty()) {
            return null;
        }
        
        // Try to find Normal variant
        $normal = $variants->first(function ($variant) {
            $name = strtolower($variant->name ?? '');
            return !str_contains($name, 'reverse') 
                && !str_contains($name, '1st') 
                && !str_contains($name, 'promo');
        });
        
        if ($normal) {
            return $normal;
        }
        
        // Try to find Unlimited variant
        $unlimited = $variants->first(function ($variant) {
            $name = strtolower($variant->name ?? '');
            return str_contains($name, 'unlimited');
        });
        
        if ($unlimited) {
            return $unlimited;
        }
        
        // Return first variant
        return $variants->first();
    }
    
    /**
     * Get other TCGCSV variants of the same card (same card number in same set, different printing)
     * More reliable than matching by name since TCGCSV includes printing info in the name itself
     */
    public function getTcgcsvVariants()
    {
        // Se non ha card_number, fallback al nome
        if (empty($this->card_number)) {
            return self::where('name', $this->name)
                ->where('group_id', $this->group_id)
                ->where('product_id', '!=', $this->product_id)
                ->with('prices')
                ->get();
        }
        
        // Usa card_number + group_id per trovare varianti (Normal, Reverse Holo, ecc.)
        return self::where('card_number', $this->card_number)
            ->where('group_id', $this->group_id)
            ->where('product_id', '!=', $this->product_id)
            ->with('prices')
            ->get();
    }
    
    /**
     * Check if this product has other TCGCSV variants
     */
    public function hasTcgcsvVariants(): bool
    {
        if (empty($this->card_number)) {
            return self::where('name', $this->name)
                ->where('group_id', $this->group_id)
                ->where('product_id', '!=', $this->product_id)
                ->exists();
        }
        
        return self::where('card_number', $this->card_number)
            ->where('group_id', $this->group_id)
            ->where('product_id', '!=', $this->product_id)
            ->exists();
    }

    /**
     * Users who liked this product
     */
    public function likedByUsers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'user_likes', 'product_id', 'user_id')
            ->withTimestamps()
            ->withPivot('created_at');
    }

    /**
     * Users who added this product to wishlist
     */
    public function wishlistedByUsers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'user_wishlist_items', 'product_id', 'user_id')
            ->withTimestamps()
            ->withPivot('created_at');
    }

    /**
     * Users watching this product
     */
    public function watchedByUsers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'user_watch_items', 'product_id', 'user_id')
            ->withTimestamps()
            ->withPivot('created_at');
    }

    /**
     * Check if product is liked by specific user
     */
    public function isLikedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->likedByUsers()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if product is in user's wishlist
     */
    public function isInWishlist(?User $user): bool
    {
        if (!$user) return false;
        return $this->wishlistedByUsers()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if product is watched by user
     */
    public function isWatchedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->watchedByUsers()->where('user_id', $user->id)->exists();
    }

    /**
     * Refresh the visible_lookup_key for this product based on current data.
     * 
     * This method computes the lookup key from the product's game, set abbreviation,
     * and card number, then updates the database if the key has changed.
     * Will not overwrite an existing key with null if inputs are incomplete.
     *
     * @return void
     */
    public function refreshVisibleLookupKey(): void
    {
        // Get set code from group's abbreviation
        $setCode = $this->group?->abbreviation;
        
        // Use space for empty abbreviations
        if ($setCode === '') {
            $setCode = ' ';
        }
        
        // Use card_number as the raw number
        $numberRaw = $this->card_number;
        
        // TODO: Get total cards from group or calculate
        $totalCards = null;
        
        // Compute the new key
        $newKey = \App\Support\VisibleCardKey::make(
            $setCode,
            $numberRaw,
            $totalCards
        );
        
        // Only update if:
        // 1. New key is not null
        // 2. New key is different from current key
        if ($newKey !== null && $this->visible_lookup_key !== $newKey) {
            $this->visible_lookup_key = $newKey;
            $this->save();
        }
    }

    /**
     * Backfill visible_lookup_key for all records that have sufficient data
     * but are missing the key.
     *
     * This method processes records in chunks, using transactions for safety.
     * It only updates records where the required fields (group abbreviation and card_number)
     * are present.
     *
     * @param int $chunk Number of records to process per chunk
     * @return void
     */
    public static function backfillVisibleLookupKeys(int $chunk = 1000): void
    {
        // Query records that:
        // 1. Have visible_lookup_key as null
        // 2. Have card_number present
        // 3. Have a group with abbreviation
        self::query()
            ->whereNull('visible_lookup_key')
            ->whereNotNull('card_number')
            ->whereHas('group', function ($query) {
                $query->whereNotNull('abbreviation');
            })
            ->with(['group', 'game'])
            ->chunkById($chunk, function ($products) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($products) {
                    foreach ($products as $product) {
                        // Get set code from group abbreviation
                        $setCode = $product->group?->abbreviation;
                        
                        // Use space for empty abbreviations
                        if ($setCode === '') {
                            $setCode = ' ';
                        }
                        
                        // Use card_number as raw number
                        $numberRaw = $product->card_number;
                        
                        // Skip if missing required data (null, but empty string is ok as space)
                        if ($setCode === null || empty($numberRaw)) {
                            continue;
                        }
                        
                        // TODO: Get total cards from group or calculate
                        $totalCards = null;
                        
                        // Compute key
                        $key = \App\Support\VisibleCardKey::make(
                            $setCode,
                            $numberRaw,
                            $totalCards
                        );
                        
                        // Update if key was generated
                        if ($key !== null) {
                            $product->visible_lookup_key = $key;
                            $product->save();
                        }
                    }
                });
            });
    }

    /**
     * Get the Game relationship
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id', 'id');
    }
}

