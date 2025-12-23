<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardCatalog extends Model
{
    protected $table = 'card_catalog';
    
    protected $fillable = [
        'game_id',
        'name',
        'set_name',
        'set_code',
        'collector_number',
        'rarity',
        'type_line',
        'image_url',
        'extra_data',
    ];

    protected $casts = [
        'extra_data' => 'array',
    ];

    /**
     * Get deck cards using this card
     */
    public function deckCards(): HasMany
    {
        return $this->hasMany(DeckCard::class, 'card_catalog_id');
    }
}
