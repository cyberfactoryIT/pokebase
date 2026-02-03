<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWatchItem extends Model
{
    protected $table = 'user_watch_items';
    
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'product_id',
        'tcgdex_card_id',
        'cmapi_card_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the TCGCSV product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(TcgcsvProduct::class, 'product_id', 'product_id');
    }

    /**
     * Get the TCGDEX card
     */
    public function tcgdexCard(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tcgdx\TcgdxCard::class, 'tcgdex_card_id');
    }

    /**
     * Get the CMAPI card
     */
    public function cmapiCard(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Cmapi\CmapiCard::class, 'cmapi_card_id', 'cmapi_id');
    }
}
