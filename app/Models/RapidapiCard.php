<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RapidapiCard extends Model
{
    protected $table = 'rapidapi_cards';
    
    protected $fillable = [
        'card_id',
        'name',
        'supertype',
        'hp',
        'types',
        'rarity',
        'artist',
        'image_url',
        'tcgo_url',
        'cardmarket_url',
        'episode_id',
        'episode_name',
        'raw_data',
        'links',
        'visible_lookup_key',
    ];
    
    protected $casts = [
        'types' => 'array',
        'raw_data' => 'array',
        'links' => 'array',
    ];
    
    /**
     * Get TCGCSV products that map to this RapidAPI card
     */
    public function tcgcsvProducts(): HasMany
    {
        return $this->hasMany(\App\Models\TcgcsvProduct::class, 'rapidapi_card_id', 'id');
    }

    /**
     * Refresh the visible_lookup_key for this card based on current data.
     * 
     * This method computes the lookup key from the card's episode slug
     * and card number, then updates the database if the key has changed.
     * Will not overwrite an existing key with null if inputs are incomplete.
     *
     * @return void
     */
    public function refreshVisibleLookupKey(): void
    {
        // Get set code from episode_slug
        $setCode = $this->episode_slug;
        
        // Use space for empty episode_slug
        if ($setCode === '') {
            $setCode = ' ';
        }
        
        // Use card_number as the raw number
        $numberRaw = $this->card_number;
        
        // Get total cards from episode JSON
        $totalCards = null;
        if ($this->episode) {
            $episodeData = is_string($this->episode) ? json_decode($this->episode, true) : $this->episode;
            $totalCards = $episodeData['cards_printed_total'] ?? $episodeData['cards_total'] ?? null;
        }
        
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
     * It only updates records where the required fields (episode_slug and card_number)
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
        // 3. Have episode_slug present (null or empty string)
        self::query()
            ->whereNull('visible_lookup_key')
            ->whereNotNull('card_number')
            ->chunkById($chunk, function ($cards) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($cards) {
                    foreach ($cards as $card) {
                        // Get set code from episode_slug
                        $setCode = $card->episode_slug;
                        
                        // Use space for empty episode_slug
                        if ($setCode === '') {
                            $setCode = ' ';
                        }
                        
                        // Use card_number as raw number
                        $numberRaw = $card->card_number;
                        
                        // Skip if missing required data (null, but empty string is ok as space)
                        if ($setCode === null || empty($numberRaw)) {
                            continue;
                        }
                        
                        // Get total cards from episode JSON
                        $totalCards = null;
                        if ($card->episode) {
                            $episodeData = is_string($card->episode) ? json_decode($card->episode, true) : $card->episode;
                            $totalCards = $episodeData['cards_printed_total'] ?? $episodeData['cards_total'] ?? null;
                        }
                        
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
