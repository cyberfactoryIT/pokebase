<?php

namespace Database\Factories;

use App\Models\DeckEvaluationPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeckEvaluationPackageFactory extends Factory
{
    protected $model = DeckEvaluationPackage::class;

    public function definition(): array
    {
        return [
            'code' => 'test_package',
            'name' => 'Test Package',
            'max_cards' => 60,
            'validity_days' => 30, // 1 mese default
            'allows_multiple_decks' => false,
            'price_cents' => 999,
            'currency' => 'EUR',
            'meta' => [],
            'is_active' => true,
        ];
    }

    /**
     * Package with 1 month validity (primi due piani).
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'validity_days' => 30,
        ]);
    }

    /**
     * Package with 1 year validity (terzo piano unlimited).
     */
    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'unlimited_package',
            'name' => 'Unlimited Package',
            'validity_days' => 365,
            'allows_multiple_decks' => true,
        ]);
    }
}
