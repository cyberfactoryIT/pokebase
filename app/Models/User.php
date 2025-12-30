<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;
    protected $guard_name = 'web';
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
        'locale',
        'theme',
        'preferred_currency',
        'email_verification_token',
        'email_verification_expires_at',
        'default_game_id',
    ];
    /**
     * Get the user's preferred locale.
     */
    public function getLocale()
    {
        return $this->locale ?? config('app.locale');
    }

    /**
     * Set the user's preferred locale.
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        $this->save();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    protected $guarded = [];

    /**
     * Override default password reset notification to use custom template.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\CustomResetPasswordNotification($token));
    }
    

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Return a display name for the user's organization.
     * If organizations are disabled, return the user's name as fallback.
     */
    public function getOrganizationNameAttribute()
    {
        if (config('organizations.enabled') && $this->organization) {
            return $this->organization->name;
        }

        return $this->name;
    }

    /**
     * Get all decks for this user
     */
    public function decks()
    {
        return $this->hasMany(\App\Models\Deck::class);
    }

    /**
     * Get all collection items for this user
     */
    public function collection()
    {
        return $this->hasMany(\App\Models\UserCollection::class);
    }

    /**
     * Get all games enabled for this user
     */
    public function games()
    {
        return $this->belongsToMany(\App\Models\Game::class, 'game_user')
            ->withTimestamps();
    }

    /**
     * Get the default game for this user
     */
    public function defaultGame()
    {
        return $this->belongsTo(\App\Models\Game::class, 'default_game_id');
    }

    /**
     * Check if user has a specific game enabled
     */
    public function hasGame($gameCode): bool
    {
        return $this->games()->where('code', $gameCode)->exists();
    }

    /**
     * Check if user has any games enabled
     */
    public function hasAnyGames(): bool
    {
        return $this->games()->count() > 0;
    }

    /**
     * Get maximum number of active games allowed for this user based on subscription tier
     * 
     * @return int|null null means unlimited (premium)
     */
    public function maxActiveGames(): ?int
    {
        $tier = $this->subscriptionTier();
        
        return match($tier) {
            'free' => 1,
            'advanced' => 3,
            'premium' => null, // unlimited
            default => 1, // fallback to free tier
        };
    }

    /**
     * Get count of currently active games
     */
    public function activeGamesCount(): int
    {
        return $this->games()->count();
    }

    /**
     * Check if user can activate another game
     */
    public function canActivateAnotherGame(): bool
    {
        $max = $this->maxActiveGames();
        
        // null means unlimited (premium)
        if ($max === null) {
            return true;
        }
        
        return $this->activeGamesCount() < $max;
    }

    /**
     * Check if user can use a specific game (game must be active for the user)
     * 
     * @param \App\Models\Game $game
     * @return bool
     */
    public function canUseGame(\App\Models\Game $game): bool
    {
        return $this->games()->where('games.id', $game->id)->exists();
    }

    /**
     * Get maximum number of cards allowed for this user based on subscription tier
     * 
     * @return int|null null means unlimited (advanced/premium)
     */
    public function cardLimit(): ?int
    {
        $tier = $this->subscriptionTier();
        
        return match($tier) {
            'free' => config('limits.cards.free', 100), // Configurable, default 100
            'advanced' => null, // unlimited
            'premium' => null, // unlimited
            default => config('limits.cards.free', 100), // fallback to free tier limit
        };
    }

    /**
     * Get current card usage (total unique cards across collection + decks)
     * For each card (product_id), we take the maximum quantity between collection and decks
     * to avoid counting the same card twice if it appears in both places.
     * 
     * @return int
     */
    public function currentCardUsage(): int
    {
        // Get all product_ids from collection with their quantities
        $collectionCards = $this->collection()
            ->select('product_id', \DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->pluck('total_quantity', 'product_id');
        
        // Get all product_ids from deck_cards with their quantities
        $deckCards = \DB::table('deck_cards')
            ->whereIn('deck_id', function($query) {
                $query->select('id')
                    ->from('decks')
                    ->where('user_id', $this->id);
            })
            ->select('product_id', \DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->pluck('total_quantity', 'product_id');
        
        // Merge and take max quantity for each product_id to avoid double counting
        $totalCount = 0;
        $allProductIds = $collectionCards->keys()->merge($deckCards->keys())->unique();
        
        foreach ($allProductIds as $productId) {
            $collectionQty = $collectionCards->get($productId, 0);
            $deckQty = $deckCards->get($productId, 0);
            
            // Take the maximum between collection and deck to avoid double counting
            $totalCount += max($collectionQty, $deckQty);
        }
        
        return $totalCount;
    }

    /**
     * Get remaining card slots for this user
     * 
     * @return int|null null means unlimited
     */
    public function remainingCardSlots(): ?int
    {
        $limit = $this->cardLimit();
        
        // null means unlimited
        if ($limit === null) {
            return null;
        }
        
        $usage = $this->currentCardUsage();
        $remaining = $limit - $usage;
        
        return max(0, $remaining);
    }

    /**
     * Check if user can add more cards
     * 
     * @param int $amount Number of cards to add
     * @return bool
     */
    public function canAddMoreCards(int $amount = 1): bool
    {
        $limit = $this->cardLimit();
        
        // null means unlimited (advanced/premium)
        if ($limit === null) {
            return true;
        }
        
        $usage = $this->currentCardUsage();
        
        return ($usage + $amount) <= $limit;
    }

    /**
     * Get all deck evaluation sessions for this user
     */
    public function deckEvaluationSessions()
    {
        return $this->hasMany(\App\Models\DeckEvaluationSession::class);
    }

    /**
     * Get all deck evaluation purchases for this user
     */
    public function deckEvaluationPurchases()
    {
        return $this->hasMany(\App\Models\DeckEvaluationPurchase::class);
    }

    /**
     * Get active deck evaluation purchase
     */
    public function activeDeckEvaluationPurchase()
    {
        return $this->deckEvaluationPurchases()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->orderBy('expires_at', 'desc');
    }

    /**
     * Check if user has active deck evaluation purchase
     */
    public function hasActiveDeckEvaluationPurchase(): bool
    {
        return $this->activeDeckEvaluationPurchase()->exists();
    }

    /**
     * Check if user can see prices in catalog/collection/deck/card pages
     * 
     * Business rule: User can see prices if:
     * - Membership tier is ADVANCED or PREMIUM
     * OR
     * - User has an ACTIVE deck evaluation purchase (not expired)
     */
    public function canSeePrices(): bool
    {
        // Check membership tier
        if ($this->isAdvanced() || $this->isPremium()) {
            return true;
        }

        // Check for active deck evaluation purchase
        return $this->hasActiveDeckEvaluationPurchase();
    }

    /**
     * Get subscription tier (free, advanced, premium)
     */
    public function subscriptionTier(): string
    {
        if (!$this->organization_id) {
            return 'free';
        }

        // Get fresh organization with pricing plan
        $org = Organization::with('pricingPlan')->find($this->organization_id);
        
        if (!$org || !$org->pricingPlan) {
            return 'free';
        }
        
        $plan = $org->pricingPlan;

        // Map pricing plan names to tiers
        $planName = strtolower($plan->name ?? '');
        
        if (str_contains($planName, 'premium')) {
            return 'premium';
        }
        
        if (str_contains($planName, 'advanced') || str_contains($planName, 'pro')) {
            return 'advanced';
        }

        return 'free';
    }

    /**
     * Check if user is on free tier
     */
    public function isFree(): bool
    {
        return $this->subscriptionTier() === 'free';
    }

    /**
     * Check if user is on advanced tier
     */
    public function isAdvanced(): bool
    {
        return $this->subscriptionTier() === 'advanced';
    }

    /**
     * Check if user is on premium tier
     */
    public function isPremium(): bool
    {
        return $this->subscriptionTier() === 'premium';
    }

    /**
     * Get membership status details
     */
    public function membershipStatus(): array
    {
        if (!$this->organization_id) {
            return [
                'tier' => 'free',
                'status' => 'active',
                'billing_period' => null,
                'next_renewal' => null,
                'is_cancelled' => false,
            ];
        }

        // Get fresh organization with pricing plan
        $org = Organization::with('pricingPlan')->find($this->organization_id);
        
        if (!$org) {
            return [
                'tier' => 'free',
                'status' => 'active',
                'billing_period' => null,
                'next_renewal' => null,
                'is_cancelled' => false,
            ];
        }
        
        $plan = $org->pricingPlan;

        return [
            'tier' => $this->subscriptionTier(),
            'plan_name' => $plan->name ?? 'Free',
            'status' => $org->subscription_cancelled ? 'cancelled' : 'active',
            'billing_period' => $org->billing_period ?? null,
            'next_renewal' => $org->renew_date,
            'is_cancelled' => (bool) $org->subscription_cancelled,
            'cancellation_date' => $org->cancellation_subscription_date,
        ];
    }

    
     /**
     * Override default email verification notification to use custom template.
     */
    public function sendEmailVerificationNotification()
    {
        // Use the same token logic as registration
        if (!$this->email_verification_token || $this->email_verification_expires_at < now()) {
            $this->email_verification_token = \Str::random(32);
            $this->email_verification_expires_at = now()->addHours(24);
            $this->save();
        }
        \Log::info('Invio notifica verifica email', [
            'user_id' => $this->id,
            'email' => $this->email,
            'token' => $this->email_verification_token,
            'expires_at' => $this->email_verification_expires_at,
        ]);
        $this->notify(new \App\Notifications\VerifyEmailNotification($this->email_verification_token));
    }
     
}
