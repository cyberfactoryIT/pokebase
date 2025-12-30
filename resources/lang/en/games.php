<?php

return [
    // Game activation limits
    'limit' => [
        'reached' => [
            'title' => 'Game Limit Reached',
            'body' => 'Your :tier membership allows :max active game(s). Deactivate a game or upgrade your plan to activate more.',
            'body_at_limit' => 'Your :tier membership allows :max active game(s). You have reached the limit.',
        ],
        'cta_upgrade' => 'Upgrade to Premium',
        'upgrade_benefits' => 'Upgrade to Advanced (3 games) or Premium (unlimited games)',
    ],
    
    // Activation messages
    'activation' => [
        'success' => 'Game activated successfully!',
        'not_allowed' => 'You cannot activate this game. Limit reached for your membership tier.',
        'must_have_one' => 'You must have at least one active game.',
    ],
    
    // Usage messages
    'usage' => [
        'not_active' => 'This game is not active for your account.',
        'activate_first' => 'Please activate this game in your profile to use it.',
        'or_upgrade' => 'Or upgrade your membership to activate more games.',
    ],
    
    // Tier display
    'tier' => [
        'free' => 'Free',
        'advanced' => 'Advanced',
        'premium' => 'Premium',
    ],
    
    // Limits display
    'limits' => [
        'free' => '1 game',
        'advanced' => '3 games',
        'premium' => 'Unlimited games',
    ],
];
