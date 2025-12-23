<?php 
return [

    'type' => [
        'login' => 'Login', 
        'user' => 'Brugeradministration', 
        'billing' => 'Fakturering',
        'organization' => 'Organisation',
        'subscription' => 'Abonnement',

        
        
    ],
    'action' => [
        // Billing già presente
        'change_plan'             => 'Skift plan',
        'reactivate_subscription' => 'Genaktiver abonnement',
        'cancel_subscription'     => 'Annuller abonnement',

        // Login/logout
        'user_login'  => 'Bruger login',
        'user_logout' => 'Bruger logout',

        // Gestione utenti
        'create' => 'Bruger oprettet',
        'update' => 'Bruger opdateret',
        'delete' => 'Bruger slettet',

        // Gestione organizzazione
        'update_organization' => 'Organisation opdateret',
        
    ],
    'data' => [
        'user' => 'Bruger',
        'organization_id' => 'Organisations-ID',
        'organization_name' => 'Organisationsnavn',
        'organization' => 'Organisationsnavn',
        'organization_code' => 'Organisationskode',
        'plan' => 'Plan',
        'previous_plan' => 'Tidligere plan',
        'new_plan' => 'Ny plan',
        'reason' => 'Årsag',
        'cancellation_date' => 'Annulleringsdato',
        'reactivation_date' => 'Genaktiveringsdato',
        'subscription_end_date' => 'Abonnements slutdato',
        'subscription_renewal_date' => 'Abonnements fornyelsesdato',
        'date' => 'Dato',
        'promotion_code' => 'Kampagnekode',
        'promotion_end_date' => 'Kampagne slutdato',
        'previous_promotion_code' => 'Tidligere kampagnekode',
        'previous_promotion_end_date' => 'Tidligere kampagne slutdato',
        'period' => 'Faktureringsperiode',
        'coupon_code' => 'Kuponkode',
        'new_plan_id' => 'Ny plan-ID',
        'old_plan_id' => 'Gammel plan-ID',
        'discount_cents' => 'Rabatbeløb (øre)',
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