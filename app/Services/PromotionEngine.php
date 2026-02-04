<?php
namespace App\Services;

use App\Models\Promotion;
use App\Models\PricingPlan;
use App\Models\Organization;
use Carbon\Carbon;

class PromotionEngine
{
    public function resolveApplicable(PricingPlan $plan, ?Organization $org = null, ?string $coupon = null, ?Carbon $at = null)
    {
    $at = $at ?: now();
    $query = Promotion::query()->activeInWindow($at);
        if ($coupon) {
            $query->whereRaw('LOWER(code) = ?', [strtolower($coupon)]);
        }
        \Log::info('PromotionEngine: query base', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);
    
    return $query->first();
    }

    public function applyToAmount(Promotion $promo, int $amount): int
    {
        if ($promo->type === 'percent') {
            return (int)round($amount * ($promo->value / 10000));
        }
        if ($promo->type === 'fixed') {
            return min($promo->value, $amount);
        }
        return 0;
    }

    public function recordRedemption(?Organization $org, ?Promotion $promo, ?string $coupon = null)
    {
        if ($promo && $org) {
            $org->promotions()->attach($promo->id, [
                'redeemed_at' => now(),
                'coupon_code' => $coupon,
                'meta' => json_encode([]),
            ]);
        }
    }
    
    /**
     * Validate and redeem a trial code
     * 
     * @throws \Exception if code is invalid or already used
     */
    public function redeemTrialCode(string $code, Organization $org): Promotion
    {
        // Find active trial promotion with this code
        $promotion = Promotion::where('type', 'trial')
            ->whereRaw('LOWER(code) = ?', [strtolower($code)])
            ->where('active', true)
            ->first();
            
        if (!$promotion) {
            throw new \Exception('Invalid trial code');
        }
        
        // Check if promotion is within date range
        if ($promotion->starts_at && $promotion->starts_at->isFuture()) {
            throw new \Exception('This trial code is not yet active');
        }
        
        if ($promotion->ends_at && $promotion->ends_at->isPast()) {
            throw new \Exception('This trial code has expired');
        }
        
        // Check if organization already used this promotion
        if ($org->promotions()->where('promotion_id', $promotion->id)->exists()) {
            throw new \Exception('You have already used this trial code');
        }
        
        // Check if organization is already on a trial
        if ($org->isOnTrial()) {
            throw new \Exception('You are already on a trial. Wait for it to expire before using another code.');
        }
        
        // Check if organization has a paid subscription
        if ($org->stripe_subscription_id) {
            throw new \Exception('You already have an active subscription. Trial codes are only for new users.');
        }
        
        // Check max redemptions
        if ($promotion->max_redemptions) {
            $totalRedemptions = $promotion->organizations()->count();
            if ($totalRedemptions >= $promotion->max_redemptions) {
                throw new \Exception('This trial code has reached its maximum number of uses');
            }
        }
        
        // All checks passed - activate trial
        $org->activateTrial($promotion);
        
        // Record redemption
        $this->recordRedemption($org, $promotion, $code);
        
        return $promotion;
    }
}
