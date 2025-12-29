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
            'billing_period' => $this->faker->randomElement(['monthly', 'yearly']),
            'price_cents' => $this->faker->numberBetween(1000, 10000),
            'currency' => 'EUR',
            'is_active' => true,
        ];
    }

    public function free()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Free Plan',
                'price_cents' => 0,
            ];
        });
    }

    public function advanced()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Advanced Plan',
                'price_cents' => 2999,
            ];
        });
    }

    public function premium()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Premium Plan',
                'price_cents' => 9999,
            ];
        });
    }
}
