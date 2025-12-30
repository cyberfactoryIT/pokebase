<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserCollection extends Model
{
    protected $table = 'user_collection';
    
    protected $fillable = [
        'user_id',
        'product_id',
        'language',
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
     * Get the game this collection item belongs to
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the card from TCGCSV products
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(TcgcsvProduct::class, 'product_id', 'product_id');
    }

    /**
     * Get all photos for this collection item
     */
    public function photos(): HasMany
    {
        return $this->hasMany(UserCardPhoto::class, 'user_collection_id');
    }

    /**
     * Boot the model and set up event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // When deleting a collection item, also delete its photos through Eloquent
        // (this triggers the UserCardPhoto model's deleting event to remove files)
        static::deleting(function ($collection) {
            $collection->photos()->each(function ($photo) {
                $photo->delete();
            });
        });
    }
}
