<?php
namespace App\Services;

use App\Models\PricingPlan;
use App\Models\Organization;
use App\Services\PromotionEngine;
use Carbon\Carbon;

class PlanPricing
{
    public function currentPriceForPlan(PricingPlan $plan, Organization $org, ?string $coupon = null, ?Carbon $at = null, ?int $base = null): array
    {
    $base = $base ?? $plan->monthly_price_cents;
        $discount = 0;
        $applied = [];
        $engine = app(PromotionEngine::class);
        if ($coupon) {
            $promo = $engine->resolveApplicable($plan, $org, $coupon, $at);
            if ($promo) {
                $discount = $engine->applyToAmount($promo, $base);
                $applied[] = $promo;
            }
        }
        return [
            'base_cents' => $base,
            'discount_cents' => $discount,
            'final_cents' => $base - $discount,
            'applied' => $applied,
        ];
    }
}
