<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Card Limits by Membership Tier
    |--------------------------------------------------------------------------
    |
    | These values control how many cards users can save in their collection
    | and decks based on their membership tier.
    |
    */

    'cards' => [
        'free' => env('FREE_CARD_LIMIT', 100), // Free users: 100 cards total
        // Advanced and Premium have unlimited cards (null)
    ],

    /*
    |--------------------------------------------------------------------------
    | Deck Limits by Membership Tier
    |--------------------------------------------------------------------------
    |
    | These values control how many decks users can create based on their
    | membership tier. Free users are limited to 1 deck.
    |
    */

    'decks' => [
        'free' => 1, // Free users: 1 deck
        // Advanced and Premium have unlimited decks (null)
    ],
];
