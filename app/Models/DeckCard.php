<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeckCard extends Model
{
    protected $fillable = [
        'deck_id',
        'product_id',
        'tcgdex_card_id',
        'cmapi_card_id',
        'quantity',
        'cached_price',
        'cached_price_currency',
        'cached_price_updated_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the deck this card belongs to
     */
    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    /**
     * Get the card from tcgcsv_products
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(TcgcsvProduct::class, 'product_id', 'product_id');
    }

    /**
     * Get the card from TCGDEX cards
     */
    public function tcgdexCard(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tcgdx\TcgdxCard::class, 'tcgdex_card_id');
    }

    /**
     * Get the card from CMAPI cards (Lorcana, One Piece)
     */
    public function cmapiCard(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Cmapi\CmapiCard::class, 'cmapi_card_id', 'cmapi_id');
    }

    /**
     * Get all photos for this deck card
     */
    public function photos(): HasMany
    {
        return $this->hasMany(DeckCardPhoto::class, 'deck_card_id');
    }
}
