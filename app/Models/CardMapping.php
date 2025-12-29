<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardMapping extends Model
{
    protected $table = 'card_mappings';
    
    protected $fillable = [
        'tcgcsv_product_id',
        'rapidapi_card_id',
        'confidence_score',
        'match_method',
    ];
    
    protected $casts = [
        'tcgcsv_product_id' => 'integer',
        'rapidapi_card_id' => 'integer',
        'confidence_score' => 'decimal:2',
    ];
    
    /**
     * Get the TCGCSV product
     */
    public function tcgcsvProduct(): BelongsTo
    {
        return $this->belongsTo(TcgcsvProduct::class, 'tcgcsv_product_id', 'product_id');
    }
    
    /**
     * Get the RapidAPI card
     */
    public function rapidapiCard(): BelongsTo
    {
        return $this->belongsTo(RapidapiCard::class, 'rapidapi_card_id', 'id');
    }
}
