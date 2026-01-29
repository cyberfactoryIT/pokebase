<?php

namespace App\Models\Tcgdx;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TcgdxCard extends Model
{
    protected $table = 'tcgdx_cards';

    protected $fillable = [
        'tcgdex_id',
        'set_tcgdx_id',
        'local_id',
        'number',
        'name',
        'rarity',
        'illustrator',
        'image_small_url',
        'image_large_url',
        'types',
        'subtypes',
        'supertype',
        'hp',
        'evolves_from',
        'tcgplayer_product_id',
        'cardmarket_product_id',
        'raw',
        'visible_lookup_key',
        'price_eur',
        'price_usd',
    ];

    protected $casts = [
        'name' => 'array',
        'types' => 'array',
        'subtypes' => 'array',
        'raw' => 'array',
        'hp' => 'integer',
        'price_eur' => 'decimal:2',
        'price_usd' => 'decimal:2',
    ];

    protected $appends = ['name_en'];

    public function set(): BelongsTo
    {
        return $this->belongsTo(TcgdxSet::class, 'set_tcgdx_id');
    }

    /**
     * Get English name (accessor)
     */
    public function getNameEnAttribute(): string
    {
        // name is already cast to array by Eloquent
        if (is_array($this->name)) {
            return $this->name['en'] ?? $this->name[array_key_first($this->name)] ?? 'Unknown';
        }
        
        return $this->name ?? 'Unknown';
    }

    /**
     * Get high quality image URL (TCGdex format)
     */
    public function getHighQualityImageUrl(): ?string
    {
        if (!$this->image_large_url) {
            return null;
        }
        
        // TCGdex URLs can be extended with /high.webp for better quality
        return $this->image_large_url . '/high.webp';
    }

    /**
     * Get low quality image URL (TCGdex format)
     */
    public function getLowQualityImageUrl(): ?string
    {
        if (!$this->image_small_url) {
            return null;
        }
        
        return $this->image_small_url . '/low.webp';
    }

    /**     * Get localized name (fallback to en)
     */
    public function getLocalizedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        
        if (is_array($this->name)) {
            return $this->name[$locale] ?? $this->name['en'] ?? $this->name[array_key_first($this->name)] ?? 'Unknown';
        }
        
        return $this->name ?? 'Unknown';
    }

    /**
     * Refresh the visible_lookup_key for this card based on current data.
     * 
     * This method computes the lookup key from the card's set tcgdex_id
     * and card number, then updates the database if the key has changed.
     * Will not overwrite an existing key with null if inputs are incomplete.
     *
     * @return void
     */
    public function refreshVisibleLookupKey(): void
    {
        // Get set code from set's tcgdex_id
        $setCode = $this->set?->tcgdex_id;
        
        // Use space for empty set code
        if ($setCode === '') {
            $setCode = ' ';
        }
        
        // Use number as the raw number
        $numberRaw = $this->number;
        
        // Get total cards from set
        $totalCards = $this->set?->card_count_official ?? $this->set?->card_count_total;
        
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
     * It only updates records where the required fields (set and number) are present.
     *
     * @param int $chunk Number of records to process per chunk
     * @return void
     */
    public static function backfillVisibleLookupKeys(int $chunk = 1000): void
    {
        // Query records that:
        // 1. Have visible_lookup_key as null
        // 2. Have number present
        self::query()
            ->whereNull('visible_lookup_key')
            ->whereNotNull('number')
            ->with('set')
            ->chunkById($chunk, function ($cards) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($cards) {
                    foreach ($cards as $card) {
                        // Get set code from set's tcgdex_id
                        $setCode = $card->set?->tcgdex_id;
                        
                        // Use space for empty set code
                        if ($setCode === '') {
                            $setCode = ' ';
                        }
                        
                        // Use number as raw number
                        $numberRaw = $card->number;
                        
                        // Skip if missing required data (null, but empty string is ok as space)
                        if ($setCode === null || empty($numberRaw)) {
                            continue;
                        }
                        
                        // Get total cards from set
                        $totalCards = $card->set?->card_count_official ?? $card->set?->card_count_total;
                        
                        // Compute key
                        $key = \App\Support\VisibleCardKey::make(
                            $setCode,
                            $numberRaw,
                            $totalCards
                        );
                        
                        // Update if key was generated
                        if ($key !== null) {
                            $card->visible_lookup_key = $key;
                            $card->save();
                        }
                    }
                });
            });
    }
}
