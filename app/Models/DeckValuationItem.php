<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeckValuationItem extends Model
{
    protected $fillable = [
        'deck_valuation_id',
        'tcgcsv_product_id',
        'qty',
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    public function deckValuation(): BelongsTo
    {
        return $this->belongsTo(DeckValuation::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(TcgcsvProduct::class, 'tcgcsv_product_id', 'product_id');
    }

    // Alias for consistency
    public function tcgcsvProduct(): BelongsTo
    {
        return $this->card();
    }
}
