<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class DeckEvaluationPurchase extends Model
{
    protected $fillable = [
        'user_id',
        'guest_token',
        'package_id',
        'purchased_at',
        'expires_at',
        'cards_limit',
        'cards_used',
        'status',
        'payment_reference',
        'meta',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'expires_at' => 'datetime',
        'cards_limit' => 'integer',
        'cards_used' => 'integer',
        'meta' => 'array',
    ];

    /**
     * Get the user who made the purchase
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the package
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(DeckEvaluationPackage::class, 'package_id');
    }

    /**
     * Get evaluation runs using this purchase
     */
    public function runs(): HasMany
    {
        return $this->hasMany(DeckEvaluationRun::class, 'purchase_id');
    }

    /**
     * Check if purchase is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && $this->expires_at->isFuture();
    }

    /**
     * Check if purchase is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if purchase has available cards
     */
    public function hasAvailableCards(): bool
    {
        if ($this->cards_limit === null) {
            return true; // Unlimited
        }
        
        return $this->cards_used < $this->cards_limit;
    }

    /**
     * Get remaining cards
     */
    public function getRemainingCardsAttribute(): ?int
    {
        if ($this->cards_limit === null) {
            return null; // Unlimited
        }
        
        return max(0, $this->cards_limit - $this->cards_used);
    }

    /**
     * Increment cards used (with safety check)
     */
    public function incrementCardsUsed(int $count): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->cards_limit !== null && ($this->cards_used + $count) > $this->cards_limit) {
            return false;
        }

        $this->cards_used += $count;
        $this->save();

        // Update status if consumed
        if ($this->cards_limit !== null && $this->cards_used >= $this->cards_limit) {
            $this->status = 'consumed';
            $this->save();
        }

        return true;
    }

    /**
     * Mark as expired (called by scheduled job)
     */
    public function markExpired(): void
    {
        if ($this->status === 'active') {
            $this->status = 'expired';
            $this->save();
        }
    }

    /**
     * Scope to get active purchases
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope to get expired purchases that need status update
     */
    public function scopeNeedsExpiry($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '<=', now());
    }

    /**
     * Scope for user or guest
     */
    public function scopeForUserOrGuest($query, ?int $userId, ?string $guestToken)
    {
        return $query->where(function($q) use ($userId, $guestToken) {
            if ($userId) {
                $q->where('user_id', $userId);
            }
            if ($guestToken) {
                $q->orWhere('guest_token', $guestToken);
            }
        });
    }
}
