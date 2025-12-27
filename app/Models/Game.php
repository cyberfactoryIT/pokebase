<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'name',
        'code',
        'tcgcsv_category_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tcgcsv_category_id' => 'integer',
    ];

    /**
     * Get all decks for this game
     */
    public function decks(): HasMany
    {
        return $this->hasMany(Deck::class);
    }

    /**
     * Get all user cards for this game
     */
    public function userCards(): HasMany
    {
        return $this->hasMany(UserCollection::class);
    }

    /**
     * Get all users who have this game enabled
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'game_user')
            ->withTimestamps();
    }

    /**
     * Get all articles for this game
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Get TCGCSV groups for this game
     */
    public function tcgcsvGroups()
    {
        return TcgcsvGroup::where('category_id', $this->tcgcsv_category_id);
    }

    /**
     * Get TCGCSV products for this game
     */
    public function tcgcsvProducts()
    {
        return TcgcsvProduct::where('category_id', $this->tcgcsv_category_id);
    }

    /**
     * Helper: Get Pokemon game
     */
    public static function pokemon(): ?Game
    {
        return static::where('code', 'pokemon')->first();
    }

    /**
     * Helper: Get Magic: The Gathering game
     */
    public static function mtg(): ?Game
    {
        return static::where('code', 'mtg')->first();
    }

    /**
     * Helper: Get Yu-Gi-Oh! game
     */
    public static function yugioh(): ?Game
    {
        return static::where('code', 'yugioh')->first();
    }
}
