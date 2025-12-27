<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TcgcsvCardmarketMapping extends Model
{
    protected $table = 'tcgcsv_cardmarket_mapping';
    
    protected $fillable = [
        'tcgcsv_product_id',
        'cardmarket_metacard_id',
        'confidence_score',
        'match_method',
        'match_notes',
    ];
    
    protected $casts = [
        'tcgcsv_product_id' => 'integer',
        'cardmarket_metacard_id' => 'integer',
        'confidence_score' => 'decimal:2',
    ];
    
    /**
     * Get the TCGCSV product
     */
    public function tcgcsvProduct(): BelongsTo
    {
        return $this->belongsTo(TcgcsvProduct::class, 'tcgcsv_product_id');
    }
    
    /**
     * Get all Cardmarket product variants for this metacard
     */
    public function cardmarketVariants()
    {
        return CardmarketProduct::where('id_metacard', $this->cardmarket_metacard_id)->get();
    }
}
