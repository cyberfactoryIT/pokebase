<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DeckEvaluationPackage extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'name',
        'max_cards',
        'validity_days',
        'allows_multiple_decks',
        'price_cents',
        'currency',
        'meta',
        'is_active',
    ];

    protected $casts = [
        'max_cards' => 'integer',
        'validity_days' => 'integer',
        'allows_multiple_decks' => 'boolean',
        'price_cents' => 'integer',
        'meta' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get all purchases for this package
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(DeckEvaluationPurchase::class, 'package_id');
    }

    /**
     * Check if package is unlimited
     */
    public function isUnlimited(): bool
    {
        return $this->max_cards === null;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        $amount = $this->price_cents / 100;
        
        return match($this->currency) {
            'EUR' => '€' . number_format($amount, 2),
            'USD' => '$' . number_format($amount, 2),
            'GBP' => '£' . number_format($amount, 2),
            'DKK' => number_format($amount, 2) . ' kr',
            default => $this->currency . ' ' . number_format($amount, 2),
        };
    }

    /**
     * Scope to get only active packages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
