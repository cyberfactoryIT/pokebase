<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCollection extends Model
{
    protected $table = 'user_collection';
    
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'condition',
        'is_foil',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_foil' => 'boolean',
    ];

    /**
     * Get the user that owns this collection item
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the card from TCGCSV products
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(TcgcsvProduct::class, 'product_id', 'product_id');
    }
}
