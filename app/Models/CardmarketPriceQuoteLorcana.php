<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardmarketPriceQuoteLorcana extends Model
{
    protected $table = 'cardmarket_price_quotes_lorcana';

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
        return $this->belongsTo(CardmarketProductLorcana::class, 'cardmarket_product_id', 'cardmarket_product_id');
    }

    /**
     * Scope to get recent quotes.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('as_of_date', '>=', now()->subDays($days));
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('as_of_date', [$startDate, $endDate]);
    }
}
