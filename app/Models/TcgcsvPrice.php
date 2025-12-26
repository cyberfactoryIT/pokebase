<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TcgcsvPrice extends Model
{
    protected $table = 'tcgcsv_prices';
    
    protected $fillable = [
        'category_id',
        'game_id',
        'group_id',
        'product_id',
        'printing',
        'condition',
        'market_price',
        'low_price',
        'mid_price',
        'high_price',
        'direct_low_price',
        'snapshot_at',
        'raw',
    ];
    
    protected $casts = [
        'category_id' => 'integer',
        'group_id' => 'integer',
        'product_id' => 'integer',
        'market_price' => 'decimal:2',
        'low_price' => 'decimal:2',
        'mid_price' => 'decimal:2',
        'high_price' => 'decimal:2',
        'direct_low_price' => 'decimal:2',
        'snapshot_at' => 'datetime',
        'raw' => 'array',
    ];
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(TcgcsvProduct::class, 'product_id', 'product_id');
    }
    
    public function group(): BelongsTo
    {
        return $this->belongsTo(TcgcsvGroup::class, 'group_id', 'group_id');
    }
}
