<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PricingPlan;

class PlansSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            $plans = [
                [
                    'code' => 'free',
                    'name' => 'Free',
                    'monthly_price_cents' => 0,
                    'currency' => 'EUR',
                ],
                [
                    'code' => 'advanced',
                    'name' => 'Advanced',
                    'monthly_price_cents' => 2500,
                    'currency' => 'EUR',
                ],
                [
                    'code' => 'premium',
                    'name' => 'Premium',
                    'monthly_price_cents' => 5900,
                    'currency' => 'EUR',
                ],
            ];

            foreach ($plans as $plan) {
                PricingPlan::updateOrCreate(
                    ['code' => $plan['code']],
                    [
                        'name' => $plan['name'],
                        'monthly_price_cents' => $plan['monthly_price_cents'],
                        'yearly_price_cents' => $plan['monthly_price_cents'] * 10,
                        'currency' => $plan['currency'],
                    ]
                );
            }

            // Defensive pass: ensure yearly_price_cents = monthly_price_cents * 10 for all plans
            foreach (PricingPlan::all() as $plan) {
                $expectedYearly = $plan->monthly_price_cents * 10;
                if ($plan->yearly_price_cents !== $expectedYearly) {
                    $plan->yearly_price_cents = $expectedYearly;
                    $plan->save();
                }
            }
        });
    }
}
