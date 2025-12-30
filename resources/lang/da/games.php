<?php

return [
    // Game activation limits
    'limit' => [
        'reached' => [
            'title' => 'Spilgrænse Nået',
            'body' => 'Dit :tier-medlemskab tillader :max aktivt/aktive spil. Deaktiver et spil eller opgrader din plan for at aktivere flere.',
            'body_at_limit' => 'Dit :tier-medlemskab tillader :max aktivt/aktive spil. Du har nået grænsen.',
        ],
        'cta_upgrade' => 'Opgrader til Premium',
        'upgrade_benefits' => 'Opgrader til Advanced (3 spil) eller Premium (ubegrænsede spil)',
    ],
    
    // Activation messages
    'activation' => [
        'success' => 'Spil aktiveret succesfuldt!',
        'not_allowed' => 'Du kan ikke aktivere dette spil. Grænse nået for dit medlemskabsniveau.',
        'must_have_one' => 'Du skal have mindst ét aktivt spil.',
    ],
    
    // Usage messages
    'usage' => [
        'not_active' => 'Dette spil er ikke aktivt for din konto.',
        'activate_first' => 'Aktiver venligst dette spil i din profil for at bruge det.',
        'or_upgrade' => 'Eller opgrader dit medlemskab for at aktivere flere spil.',
    ],
    
    // Tier display
    'tier' => [
        'free' => 'Gratis',
        'advanced' => 'Advanced',
        'premium' => 'Premium',
    ],
    
    // Limits display
    'limits' => [
        'free' => '1 spil',
        'advanced' => '3 spil',
        'premium' => 'Ubegrænsede spil',
    ],
];
