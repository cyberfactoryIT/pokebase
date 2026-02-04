<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
    /**
     * Relazione con le promozioni applicate all'organizzazione.
     */
{
    use HasFactory, SoftDeletes;

    /**
     * Relazione con le promozioni applicate all'organizzazione.
     */
    public function promotions()
    {
        return $this->belongsToMany(\App\Models\Promotion::class, 'organization_promotions')
            ->withPivot(['redeemed_at','coupon_code','meta']);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'slug',
        'timezone',
        'subscription_date',
        'renew_date',
        'end_promotion_date',
    'promotion_code',
    'subscription_cancelled',
    'cancellation_subscription_date',
    'reactivate_subscription_date',
    // Payment type fields
    'payment_type',
    'stripe_customer_id',
    'stripe_subscription_id',
    // Billing fields
    'company',
    'billing_email',
    'vat_number',
    'address_line1',
    'address_line2',
    'city',
    'postcode',
    'country',
    // Trial fields
    'trial_plan_id',
    'trial_expires_at',
    'trial_promotion_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'subscription_date' => 'datetime',
        'renew_date' => 'datetime',
        'end_promotion_date' => 'datetime',
        'cancellation_subscription_date' => 'datetime',
        'reactivate_subscription_date' => 'datetime',
        'trial_expires_at' => 'datetime',
    ];

    /**
     * Get the users for the organization.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }
    /**
     * Get the invoices for the organization.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Invoice::class);
    }
    
    /**
     * Get the pricing plan for the organization.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pricingPlan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\PricingPlan::class, 'pricing_plan_id');
    }
    /**
     * Applica una promozione all'organizzazione e aggiorna i campi di tracking.
     */
    public function applyPromotion(Promotion $promotion, $endDate = null, $code = null)
    {
    $this->promotion_code = $code ?? $promotion->code;
    $this->end_promotion_date = $this->subscription_date ? \Carbon\Carbon::parse($this->subscription_date)->addYear() : now()->addYear();
        $this->save();
        // Associa la promozione se non già associata
        if (!$this->promotions()->where('promotion_id', $promotion->id)->exists()) {
            $this->promotions()->attach($promotion->id, [
                'redeemed_at' => now(),
                'coupon_code' => $this->promotion_code,
            ]);
        }
    }
    
    /**
     * Trial plan relationship
     */
    public function trialPlan()
    {
        return $this->belongsTo(PricingPlan::class, 'trial_plan_id');
    }
    
    /**
     * Trial promotion relationship
     */
    public function trialPromotion()
    {
        return $this->belongsTo(Promotion::class, 'trial_promotion_id');
    }
    
    /**
     * Check if organization is currently on a trial
     */
    public function isOnTrial(): bool
    {
        return $this->trial_plan_id !== null 
            && $this->trial_expires_at !== null 
            && $this->trial_expires_at->isFuture();
    }
    
    /**
     * Check if trial has expired
     */
    public function hasExpiredTrial(): bool
    {
        return $this->trial_plan_id !== null 
            && $this->trial_expires_at !== null 
            && $this->trial_expires_at->isPast();
    }
    
    /**
     * Activate a trial promotion
     */
    public function activateTrial(Promotion $promotion): void
    {
        if (!$promotion->isTrial()) {
            throw new \Exception('Promotion is not a trial type');
        }
        
        $this->update([
            'trial_plan_id' => $promotion->trial_plan_id,
            'trial_expires_at' => now()->addDays($promotion->trial_duration_days),
            'trial_promotion_id' => $promotion->id,
        ]);
        
        \Log::info('Trial activated', [
            'organization_id' => $this->id,
            'promotion_id' => $promotion->id,
            'trial_plan_id' => $promotion->trial_plan_id,
            'expires_at' => $this->trial_expires_at,
        ]);
    }
    
    /**
     * End trial and revert to free plan
     */
    public function endTrial(): void
    {
        $this->update([
            'trial_plan_id' => null,
            'trial_expires_at' => null,
            'trial_promotion_id' => null,
        ]);
        
        \Log::info('Trial ended', [
            'organization_id' => $this->id,
        ]);
    }
    
    /**
     * Get effective pricing plan (trial plan if active, otherwise current plan)
     */
    public function getEffectivePlan(): ?PricingPlan
    {
        if ($this->isOnTrial()) {
            return $this->trialPlan;
        }
        
        return $this->pricingPlan;
    }
}
