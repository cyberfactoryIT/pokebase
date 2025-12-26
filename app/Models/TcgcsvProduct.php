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
}
