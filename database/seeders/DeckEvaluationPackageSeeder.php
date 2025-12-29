<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeckEvaluationPackage;

class DeckEvaluationPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'code' => 'EVAL_100',
                'name' => '100 Cards Package',
                'max_cards' => 100,
                'validity_days' => 30,
                'allows_multiple_decks' => false,
                'price_cents' => 999, // €9.99
                'currency' => 'EUR',
                'is_active' => true,
                'meta' => [
                    'features' => [
                        'max_cards' => 100,
                        'validity' => '1 month',
                        'multiple_decks' => false,
                    ],
                ],
            ],
            [
                'code' => 'EVAL_600',
                'name' => '600 Cards Package',
                'max_cards' => 600,
                'validity_days' => 30,
                'allows_multiple_decks' => false,
                'price_cents' => 4999, // €49.99
                'currency' => 'EUR',
                'is_active' => true,
                'meta' => [
                    'features' => [
                        'max_cards' => 600,
                        'validity' => '1 month',
                        'multiple_decks' => false,
                    ],
                ],
            ],
            [
                'code' => 'EVAL_UNLIMITED',
                'name' => 'Unlimited Package',
                'max_cards' => null,
                'validity_days' => 365,
                'allows_multiple_decks' => true,
                'price_cents' => 9999, // €99.99
                'currency' => 'EUR',
                'is_active' => true,
                'meta' => [
                    'features' => [
                        'max_cards' => 'unlimited',
                        'validity' => '1 year',
                        'multiple_decks' => true,
                    ],
                ],
            ],
        ];

        foreach ($packages as $package) {
            DeckEvaluationPackage::updateOrCreate(
                ['code' => $package['code']],
                $package
            );
        }
    }
}
