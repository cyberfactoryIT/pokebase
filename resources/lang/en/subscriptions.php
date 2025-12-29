<?php

return [
    // Membership Section
    'membership' => [
        'title' => 'Membership (Recurring)',
        'current_plan' => 'Current Plan',
        'status' => 'Status',
        'billing_period' => 'Billing Period',
        'next_renewal' => 'Next Renewal',
        'no_active_membership' => 'No active membership subscription',
        'explanation' => 'Your membership subscription provides access to premium features with recurring billing.',
        
        // Status values
        'status_active' => 'Active',
        'status_cancelled' => 'Cancelled',
        'status_past_due' => 'Past Due',
        'status_expired' => 'Expired',
        
        // Billing periods
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
        
        // Actions
        'change_plan' => 'Change Plan',
        'cancel_subscription' => 'Cancel Subscription',
        'reactivate_subscription' => 'Reactivate Subscription',
    ],

    // Deck Evaluation Section
    'deck_evaluation' => [
        'title' => 'Deck Evaluation (One-shot Purchases)',
        'active_purchases' => 'Active Purchases',
        'expired_purchases' => 'Expired Purchases',
        'no_purchases' => 'No deck evaluation purchases',
        'explanation' => 'Deck evaluation packages are separate one-time purchases that allow you to evaluate your card decks.',
        'coexistence_note' => 'Note: Membership and Deck Evaluation are separate products. You can have both active at the same time.',
        
        'package_name' => 'Package',
        'valid_until' => 'Valid Until',
        'cards_used' => 'Cards Used',
        'cards_limit' => 'Cards Limit',
        'unlimited_package' => 'Unlimited',
        'multiple_decks_allowed' => 'Multiple decks allowed',
        
        'go_to_deck_evaluation' => 'Go to Deck Evaluation',
        'purchase_package' => 'Purchase Package',
        
        // Advanced/Premium messaging
        'included_in_plan' => 'Deck Evaluation Included',
        'advanced_premium_note' => 'Your Advanced/Premium plan includes unlimited deck evaluations. You can evaluate as many decks as you want without purchasing additional packages.',
        'start_evaluation' => 'Start Deck Evaluation',
    ],

    // Plan Tiers
    'tiers' => [
        'free' => 'Free',
        'advanced' => 'Advanced',
        'premium' => 'Premium',
    ],
];
