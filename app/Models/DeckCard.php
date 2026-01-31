<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeckCard extends Model
{
    protected $fillable = [
        'deck_id',
        'product_id',
        'tcgdex_card_id',
        'quantity',
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
        return $this->belongsTo(TcgdxCard::class, 'tcgdex_card_id');
    }
}
