<?php

return [
    'renewal_reminder' => [
        'subject' => 'Your subscription renews in :days days',
        'greeting' => 'Hello :name,',
        'intro' => 'This is a friendly reminder that your subscription will automatically renew in :days days on :date.',
        'plan_details' => 'Plan: :plan (:period)',
        'amount' => 'Renewal amount: :amount :currency',
        'cancel_info' => 'If you wish to cancel your subscription, please do so before the renewal date to avoid charges.',
        'manage_subscription' => 'Manage Subscription',
        'thank_you' => 'Thank you for your continued support!',
    ],
    'payment_failed' => [
        'subject' => 'Payment Failed - Action Required',
        'greeting' => 'Hello :name,',
        'intro' => 'We were unable to process your subscription payment.',
        'amount' => 'Amount due: :amount :currency',
        'action_required' => 'Please update your payment method to continue your subscription.',
        'update_payment' => 'Update Payment Method',
        'warning' => 'If payment is not updated, your subscription may be cancelled.',
    ],
];
