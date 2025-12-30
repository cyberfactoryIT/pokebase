<?php

namespace Database\Factories;

use App\Models\DeckEvaluationPurchase;
use App\Models\User;
use App\Models\DeckEvaluationPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeckEvaluationPurchaseFactory extends Factory
{
    protected $model = DeckEvaluationPurchase::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'guest_token' => null,
            'package_id' => DeckEvaluationPackage::factory(), // Crea automaticamente il package
            'purchased_at' => now(),
            'expires_at' => now()->addMonth(), // Default: 1 mese (primi due piani)
            'cards_limit' => 60,
            'cards_used' => 0,
            'status' => 'active', // Valori permessi: active, expired, consumed
            'payment_reference' => 'test_' . $this->faker->uuid(),
            'meta' => [
                'test' => true,
            ],
        ];
    }

    /**
     * Indicate that the purchase is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now()->subDays(400),
            'purchased_at' => now()->subDays(400),
            'expires_at' => now()->subDays(370), // Scaduto da 370 giorni
            'status' => 'expired',
        ]);
    }

    /**
     * Indicate that the purchase is active (recent).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now()->subDays(30),
            'purchased_at' => now()->subDays(30),
            'expires_at' => now()->addMonth(), // Ancora valido per 1 mese
            'status' => 'active',
        ]);
    }

    /**
     * Unlimited plan (expires in 1 year).
     */
    public function unlimited(): static
    {
        return $this->state(fn (array $attributes) => [
            'package_id' => 3, // Piano unlimited
            'expires_at' => now()->addYear(),
            'status' => 'active',
        ]);
    }
}
