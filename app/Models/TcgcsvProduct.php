<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TcgcsvProduct extends Model
{
    protected $table = 'tcgcsv_products';
    
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
     * Get the Cardmarket metacard mapping for this product
     */
    public function cardmarketMapping()
    {
        return $this->hasOne(\App\Models\TcgcsvCardmarketMapping::class, 'tcgcsv_product_id', 'id');
    }
    
    /**
     * Get the RapidAPI card mapping
     */
    public function cardMapping()
    {
        return $this->hasOne(\App\Models\CardMapping::class, 'tcgcsv_product_id', 'product_id');
    }
    
    /**
     * Get the RapidAPI card data through mapping
     */
    public function rapidapiCard()
    {
        return $this->hasOneThrough(
            \App\Models\RapidapiCard::class,
            \App\Models\CardMapping::class,
            'tcgcsv_product_id',  // Foreign key on card_mappings
            'id',                  // Foreign key on rapidapi_cards
            'product_id',          // Local key on tcgcsv_products
            'rapidapi_card_id'     // Local key on card_mappings
        );
    }
    
    /**
     * Get all Cardmarket product variants through the metacard mapping
     */
    public function cardmarketVariants()
    {
        return $this->hasManyThrough(
            \App\Models\CardmarketProduct::class,
            \App\Models\TcgcsvCardmarketMapping::class,
            'tcgcsv_product_id', // Foreign key on mapping table
            'id_metacard',       // Foreign key on cardmarket_products table
            'id',                // Local key on tcgcsv_products table
            'cardmarket_metacard_id' // Local key on mapping table
        );
    }
    
    /**
     * Check if this product has Cardmarket variants
     */
    public function hasCardmarketVariants(): bool
    {
        return $this->cardmarketMapping()->exists() 
            && $this->cardmarketVariants()->exists();
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
}
