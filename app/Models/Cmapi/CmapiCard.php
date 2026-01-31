<?php

namespace App\Models\Cmapi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmapiCard extends Model
{
    protected $table = 'cmapi_cards';

    protected $fillable = [
        'cmapi_id',
        'game',
        'set_cmapi_id',
        'name',
        'number',
        'rarity',
        'image_small_url',
        'image_large_url',
        'price_eur',
        'price_usd',
        'ink_cost',
        'card_type',
        'lore_value',
        'ink_color',
        'cost',
        'power',
        'counter',
        'color',
        'artist_name',
        'slug',
        'tcggo_url',
        'cardmarket_id',
        'hp',
        'raw',
    ];

    protected $casts = [
        'raw' => 'array',
        'price_eur' => 'decimal:2',
        'price_usd' => 'decimal:2',
        'ink_cost' => 'integer',
        'lore_value' => 'integer',
        'cost' => 'integer',
        'power' => 'integer',
        'counter' => 'integer',
    ];

    /**
     * Set this card belongs to
     */
    public function set(): BelongsTo
    {
        return $this->belongsTo(CmapiSet::class, 'set_cmapi_id');
    }

    /**
     * Price snapshots for this card (historical pricing data)
     */
    public function priceSnapshots(): HasMany
    {
        return $this->hasMany(CmapiCardPriceSnapshot::class, 'cmapi_card_id')
            ->orderBy('recorded_at', 'desc');
    }
}
