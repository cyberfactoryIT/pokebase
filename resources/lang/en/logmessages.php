<?php 
return [

    'type' => [
        'login' => 'Login', 
        'user' => 'User Management', 
        'billing' => 'Billing',
        'organization' => 'Organization',
        'subscription' => 'Subscription',

        
        
    ],
    'action' => [
        // Billing già presente
        'change_plan'             => 'Change Plan',
        'reactivate_subscription' => 'Reactivate Subscription',
        'cancel_subscription'     => 'Cancel Subscription',

        // Login/logout
        'user_login'  => 'User Login',
        'user_logout' => 'User Logout',

        // Gestione utenti
        'create' => 'User Created',
        'update' => 'User Updated',
        'delete' => 'User Deleted',

        // Gestione organizzazione
        'update_organization' => 'Organization Updated',
        
    ],
    'data' => [
        'user' => 'User',
        'organization_id' => 'Organization ID',
        'organization_name' => 'Organization Name',
        'organization' => 'Organization Name',
        'organization_code' => 'Organization Code',
        'plan' => 'Plan',
        'previous_plan' => 'Previous Plan',
        'new_plan' => 'New Plan',
        'reason' => 'Reason',
        'cancellation_date' => 'Cancellation Date',
        'reactivation_date' => 'Reactivation Date',
        'subscription_end_date' => 'Subscription End Date',
        'subscription_renewal_date' => 'Subscription Renewal Date',
        'date' => 'Date',
        'promotion_code' => 'Promotion Code',
        'promotion_end_date' => 'Promotion End Date',
        'previous_promotion_code' => 'Previous Promotion Code',
        'previous_promotion_end_date' => 'Previous Promotion End Date',
        'period' => 'Billing Period',
        'coupon_code' => 'Coupon Code',
        'new_plan_id' => 'New Plan ID',
        'old_plan_id' => 'Old Plan ID',
        'discount_cents' => 'Discount Amount (cents)',
        /*
        logmessages.data.period: monthly
logmessages.data.coupon_code:
logmessages.data.new_plan_id: Pro
logmessages.data.old_plan_id:
logmessages.data.discount_cents: 0
^// Promozioni
        
        */
    ],
];