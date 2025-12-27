<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardmarketPriceQuote extends Model
{
    protected $fillable = [
        'cardmarket_product_id',
        'id_category',
        'as_of_date',
        'currency',
        'avg',
        'low',
        'trend',
        'avg_holo',
        'low_holo',
        'trend_holo',
        'avg1',
        'avg7',
        'avg30',
        'raw',
    ];

    protected $casts = [
        'cardmarket_product_id' => 'integer',
        'id_category' => 'integer',
        'as_of_date' => 'date',
        'avg' => 'decimal:2',
        'low' => 'decimal:2',
        'trend' => 'decimal:2',
        'avg_holo' => 'decimal:2',
        'low_holo' => 'decimal:2',
        'trend_holo' => 'decimal:2',
        'avg1' => 'decimal:2',
        'avg7' => 'decimal:2',
        'avg30' => 'decimal:2',
        'raw' => 'array',
    ];

    /**
     * Get the product this price quote belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(CardmarketProduct::class, 'cardmarket_product_id', 'cardmarket_product_id');
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('as_of_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get quotes for a specific date.
     */
    public function scopeAsOf($query, $date)
    {
        return $query->where('as_of_date', $date);
    }

    /**
     * Scope to get latest quotes.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('as_of_date', 'desc');
    }

    /**
     * Scope to filter by category.
     */
    public function scopeForCategory($query, int $categoryId)
    {
        return $query->where('id_category', $categoryId);
    }

    /**
     * Get the best available regular price (avg > trend > low).
     */
    public function getBestPriceAttribute()
    {
        return $this->avg ?? $this->trend ?? $this->low;
    }

    /**
     * Get the best available holo price (avg_holo > trend_holo > low_holo).
     */
    public function getBestHoloPriceAttribute()
    {
        return $this->avg_holo ?? $this->trend_holo ?? $this->low_holo;
    }

    /**
     * Check if this product has holo pricing.
     */
    public function hasHoloPricing(): bool
    {
        return $this->avg_holo !== null || $this->low_holo !== null || $this->trend_holo !== null;
    }
}
