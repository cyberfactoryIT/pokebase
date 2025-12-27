<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TcgcsvProduct extends Model
{
    protected $table = 'tcgcsv_products';
    
    protected $fillable = [
        'category_id',
        'game_id',
        'group_id',
        'product_id',
        'language',
        'name',
        'clean_name',
        'image_url',
        'rarity',
        'card_number',
        'modified_on',
        'extended_data',
        'raw',
    ];
    
    protected $casts = [
        'category_id' => 'integer',
        'group_id' => 'integer',
        'product_id' => 'integer',
        'modified_on' => 'datetime',
        'extended_data' => 'array',
        'raw' => 'array',
    ];
    
    public function group(): BelongsTo
    {
        return $this->belongsTo(TcgcsvGroup::class, 'group_id', 'group_id');
    }
    
    public function prices(): HasMany
    {
        return $this->hasMany(TcgcsvPrice::class, 'product_id', 'product_id');
    }
    
    /**
     * Get the Cardmarket metacard mapping for this product
     */
    public function cardmarketMapping()
    {
        return $this->hasOne(\App\Models\TcgcsvCardmarketMapping::class, 'tcgcsv_product_id', 'id');
    }
    
    /**
     * Get all Cardmarket product variants through the metacard mapping
     */
    public function cardmarketVariants()
    {
        return $this->hasManyThrough(
            \App\Models\CardmarketProduct::class,
            \App\Models\TcgcsvCardmarketMapping::class,
            'tcgcsv_product_id', // Foreign key on mapping table
            'id_metacard',       // Foreign key on cardmarket_products table
            'id',                // Local key on tcgcsv_products table
            'cardmarket_metacard_id' // Local key on mapping table
        );
    }
}
