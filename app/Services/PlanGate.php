<?php
namespace App\Services;

use App\Models\Organization;

class PlanGate
{
    public function allows(Organization $org, string $featureKey, $operator = 'bool', $default = null)
    {
        $plan = $org->pricingPlan;
        if (!$plan) return $default;
        $value = $plan->getFeatureValue($featureKey, $default);
        if ($operator === 'bool') return (bool)$value;
        if ($operator === 'int') return (int)$value;
        return $value;
    }

    public function limit(Organization $org, string $featureKey, int $fallback = 0): int
    {
        return (int)($this->allows($org, $featureKey, 'int', $fallback));
    }
}
