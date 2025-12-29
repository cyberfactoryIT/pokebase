<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'code' => strtoupper($this->faker->lexify('???')),
            'slug' => $this->faker->unique()->slug(),
            'timezone' => 'UTC',
            'subscription_date' => null,
            'renew_date' => null,
            'end_promotion_date' => null,
            'promotion_code' => null,
            'subscription_cancelled' => false,
            'cancellation_subscription_date' => null,
            'reactivate_subscription_date' => null,
            'pricing_plan_id' => null,
        ];
    }

    public function withSubscription()
    {
        return $this->state(function (array $attributes) {
            return [
                'subscription_date' => now()->subMonth(),
                'renew_date' => now()->addMonth(),
            ];
        });
    }

    public function cancelled()
    {
        return $this->state(function (array $attributes) {
            return [
                'subscription_cancelled' => true,
                'cancellation_subscription_date' => now()->subDays(5),
            ];
        });
    }
}
