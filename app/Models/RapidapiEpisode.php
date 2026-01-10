<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RapidapiEpisode extends Model
{
    protected $table = 'rapidapi_episodes';
    
    protected $fillable = [
        'episode_id',
        'game',
        'name',
        'slug',
        'code',
        'released_at',
        'logo_url',
        'cards_total',
        'cards_printed_total',
        'cards_updated_at',
        'series_id',
        'series_name',
        'cardmarket_total_value',
        'tcgplayer_total_value',
        'raw_data',
    ];
    
    protected $casts = [
        'episode_id' => 'integer',
        'released_at' => 'date',
        'cards_updated_at' => 'datetime',
        'cards_total' => 'integer',
        'cards_printed_total' => 'integer',
        'series_id' => 'integer',
        'cardmarket_total_value' => 'decimal:2',
        'tcgplayer_total_value' => 'decimal:2',
        'raw_data' => 'array',
    ];

    public function cards(): HasMany
    {
        return $this->hasMany(RapidapiCard::class, 'episode_id', 'episode_id');
    }

    public function priceHistory(): HasMany
    {
        return $this->hasMany(RapidapiPriceHistory::class, 'episode_id', 'episode_id');
    }
    
    public function tcgcsvGroups(): HasMany
    {
        return $this->hasMany(TcgcsvGroup::class, 'rapidapi_episode_id', 'episode_id');
    }
}
