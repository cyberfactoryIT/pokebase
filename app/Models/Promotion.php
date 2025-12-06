<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Promotion extends Model
{
    protected $fillable = [
        'name','code','type','value','starts_at','ends_at','active',
        'max_redemptions','per_org_limit','new_orgs_only','stackable','meta'
    ];
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'active' => 'bool',
        'new_orgs_only' => 'bool',
        'stackable' => 'bool',
        'meta' => 'array',
    ];

    public function plans()
    {
        return $this->belongsToMany(PricingPlan::class, 'promotion_pricing_plan');
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_promotions')
            ->withPivot(['redeemed_at','coupon_code','meta']);
    }

    public function scopeActiveInWindow(Builder $q, Carbon $at)
    {
        return $q->where('active', true)
            ->where(function($q) use ($at) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $at);
            })
            ->where(function($q) use ($at) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $at);
            });
    }

    public function appliesToPlan(?PricingPlan $plan): bool
    {
        if (!$plan) return false;
        return $this->plans()->where('pricing_plan_id', $plan->id)->exists();
    }
}
