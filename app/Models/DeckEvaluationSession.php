<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DeckEvaluationSession extends Model
{
    protected $fillable = [
        'user_id',
        'guest_token',
        'guest_deck_id',
        'deck_valuation_id',
        'status',
        'free_cards_limit',
        'free_cards_used',
        'meta',
    ];

    protected $casts = [
        'free_cards_limit' => 'integer',
        'free_cards_used' => 'integer',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->guest_token) && empty($model->user_id)) {
                $model->guest_token = self::generateGuestToken();
            }
        });
    }

    /**
     * Generate a secure guest token
     */
    public static function generateGuestToken(): string
    {
        return 'gst_' . Str::random(40);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the guest deck
     */
    public function guestDeck(): BelongsTo
    {
        return $this->belongsTo(GuestDeck::class);
    }

    /**
     * Get the deck valuation
     */
    public function deckValuation(): BelongsTo
    {
        return $this->belongsTo(DeckValuation::class);
    }

    /**
     * Get evaluation runs
     */
    public function runs(): HasMany
    {
        return $this->hasMany(DeckEvaluationRun::class, 'session_id');
    }

    /**
     * Check if session has used all free cards
     */
    public function hasFreeCardsRemaining(): bool
    {
        return $this->free_cards_used < $this->free_cards_limit;
    }

    /**
     * Get remaining free cards
     */
    public function getRemainingFreeCardsAttribute(): int
    {
        return max(0, $this->free_cards_limit - $this->free_cards_used);
    }

    /**
     * Increment free cards used
     */
    public function incrementFreeCardsUsed(int $count): void
    {
        $this->free_cards_used = min($this->free_cards_used + $count, $this->free_cards_limit);
        $this->save();
    }

    /**
     * Claim session to a user (when guest registers)
     */
    public function claimToUser(int $userId): void
    {
        $this->user_id = $userId;
        $this->save();
    }
}
