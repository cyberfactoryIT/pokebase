<?php

return [
    // Card limits
    'cards' => [
        'free' => [
            'limit' => '100 cards',
            'usage' => ':used of :limit cards used',
            'remaining' => ':remaining card slots remaining',
            'unlimited' => 'Unlimited cards',
        ],
        'reached' => [
            'title' => 'Card Limit Reached',
            'body' => 'Your Free membership allows up to :limit cards total (collection + decks). You currently have :used cards.',
            'body_adding' => 'Adding :amount card(s) would exceed your limit of :limit cards. You currently have :used cards.',
        ],
        'cta_upgrade' => 'Upgrade to Advanced or Premium',
        'upgrade_benefits' => 'Advanced and Premium memberships include unlimited cards in your collection and decks.',
    ],
];
