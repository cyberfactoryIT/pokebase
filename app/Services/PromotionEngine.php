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
}
