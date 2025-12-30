<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deck extends Model
{
    protected $fillable = [
        'user_id',
        'game_id',
        'name',
        'format',
        'description',
        'is_shared',
        'shared_token',
        'shared_at',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
        'shared_at' => 'datetime',
    ];

    /**
     * Get the user that owns the deck
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the game this deck belongs to
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the cards in this deck
     */
    public function deckCards(): HasMany
    {
        return $this->hasMany(DeckCard::class);
    }

    /**
     * Get total card count in deck
     */
    public function totalCards(): int
    {
        return $this->deckCards()->sum('quantity');
    }

    /**
     * Generate a unique share token
     */
    public function generateShareToken(): string
    {
        do {
            $token = \Str::random(32);
        } while (self::where('shared_token', $token)->exists());
        
        return $token;
    }

    /**
     * Share this deck (generate token and mark as shared)
     */
    public function share(): void
    {
        if (!$this->is_shared) {
            $this->shared_token = $this->generateShareToken();
            $this->is_shared = true;
            $this->shared_at = now();
            $this->save();
        }
    }

    /**
     * Unshare this deck (revoke token and mark as not shared)
     */
    public function unshare(): void
    {
        if ($this->is_shared) {
            $this->is_shared = false;
            $this->shared_token = null;
            $this->shared_at = null;
            $this->save();
        }
    }

    /**
     * Get public URL for this deck
     */
    public function getPublicUrlAttribute(): ?string
    {
        if (!$this->is_shared || !$this->shared_token) {
            return null;
        }
        
        return route('decks.public', ['token' => $this->shared_token]);
    }

    /**
     * Scope to get only shared decks
     */
    public function scopeShared($query)
    {
        return $query->where('is_shared', true)->whereNotNull('shared_token');
    }
}
