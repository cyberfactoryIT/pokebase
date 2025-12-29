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
    ];
    
    protected $casts = [
        'types' => 'array',
        'raw_data' => 'array',
        'links' => 'array',
    ];
    
    /**
     * Get the card mappings
     */
    public function cardMappings(): HasMany
    {
        return $this->hasMany(CardMapping::class, 'rapidapi_card_id', 'id');
    }
}
