<?php

return [
    // Card limits
    'cards' => [
        'free' => [
            'limit' => '100 kort',
            'usage' => ':used af :limit kort brugt',
            'remaining' => ':remaining kort pladser tilbage',
            'unlimited' => 'Ubegrænsede kort',
        ],
        'reached' => [
            'title' => 'Kortgrænse Nået',
            'body' => 'Dit Gratis-medlemskab tillader op til :limit kort i alt (samling + dæk). Du har i øjeblikket :used kort.',
            'body_adding' => 'Tilføjelse af :amount kort vil overstige din grænse på :limit kort. Du har i øjeblikket :used kort.',
        ],
        'cta_upgrade' => 'Opgrader til Advanced eller Premium',
        'upgrade_benefits' => 'Advanced og Premium-medlemskaber inkluderer ubegrænsede kort i din samling og dæk.',
    ],
];
