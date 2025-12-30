<?php

namespace Database\Factories;

use App\Models\PricingPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PricingPlanFactory extends Factory
{
    protected $model = PricingPlan::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Free', 'Advanced', 'Premium']) . ' Plan',
            'code' => strtolower($this->faker->randomElement(['free', 'advanced', 'premium'])),
            'monthly_price_cents' => $this->faker->numberBetween(0, 10000),
            'yearly_price_cents' => $this->faker->numberBetween(0, 100000),
            'currency' => 'EUR',
            'meta' => [],
        ];
    }

    public function free()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Free',
                'code' => 'free',
                'monthly_price_cents' => 0,
                'yearly_price_cents' => 0,
            ];
        });
    }

    public function advanced()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Advanced',
                'code' => 'advanced',
                'monthly_price_cents' => 2999,
                'yearly_price_cents' => 29990,
            ];
        });
    }

    public function premium()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Premium',
                'code' => 'premium',
                'monthly_price_cents' => 9999,
                'yearly_price_cents' => 99990,
            ];
        });
    }
}
