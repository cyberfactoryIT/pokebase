<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardmarketProduct extends Model
{
    protected $fillable = [
        'cardmarket_product_id',
        'id_category',
        'category_name',
        'id_expansion',
        'id_metacard',
        'name',
        'date_added',
        'raw',
        'tcgcsv_product_id',
    ];

    protected $casts = [
        'cardmarket_product_id' => 'integer',
        'id_category' => 'integer',
        'id_expansion' => 'integer',
        'id_metacard' => 'integer',
        'date_added' => 'date',
        'raw' => 'array',
    ];

    /**
     * Get the expansion this product belongs to.
     */
    public function expansion()
    {
        return $this->belongsTo(CardmarketExpansion::class, 'id_expansion', 'cardmarket_expansion_id');
    }

    /**
     * Get all price quotes for this product.
     */
    public function priceQuotes(): HasMany
    {
        return $this->hasMany(CardmarketPriceQuote::class, 'cardmarket_product_id', 'cardmarket_product_id');
    }

    /**
     * Get the latest price quote.
     */
    public function latestPriceQuote()
    {
        return $this->hasOne(CardmarketPriceQuote::class, 'cardmarket_product_id', 'cardmarket_product_id')
            ->latest('as_of_date');
    }

    /**
     * Scope to filter by category.
     */
    public function scopeForCategory($query, int $categoryId)
    {
        return $query->where('id_category', $categoryId);
    }

    /**
     * Scope to filter by category name.
     */
    public function scopeForCategoryName($query, string $categoryName)
    {
        return $query->where('category_name', $categoryName);
    }

    /**
     * Scope to filter by expansion.
     */
    public function scopeForExpansion($query, int $expansionId)
    {
        return $query->where('id_expansion', $expansionId);
    }

    /**
     * Scope to filter by metacard.
     */
    public function scopeForMetacard($query, int $metacardId)
    {
        return $query->where('id_metacard', $metacardId);
    }
}
