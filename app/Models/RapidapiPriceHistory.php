<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RapidapiPriceHistory extends Model
{
    protected $table = 'rapidapi_price_history';

    protected $fillable = [
        'card_id',
        'episode_id',
        'game',
        'snapshot_date',
        'cardmarket_avg',
        'cardmarket_low',
        'cardmarket_high',
        'cardmarket_trend',
        'tcgplayer_market',
        'tcgplayer_low',
        'tcgplayer_high',
        'tcgplayer_mid',
        'raw_data',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'raw_data' => 'array',
        'cardmarket_avg' => 'decimal:2',
        'cardmarket_low' => 'decimal:2',
        'cardmarket_high' => 'decimal:2',
        'cardmarket_trend' => 'decimal:2',
        'tcgplayer_market' => 'decimal:2',
        'tcgplayer_low' => 'decimal:2',
        'tcgplayer_high' => 'decimal:2',
        'tcgplayer_mid' => 'decimal:2',
    ];

    public function episode()
    {
        return $this->belongsTo(RapidapiEpisode::class, 'episode_id', 'episode_id');
    }

    public function card()
    {
        return $this->belongsTo(RapidapiPrice::class, 'card_id', 'card_id');
    }
}
