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
        // Billing allerede til stede
        'change_plan'             => 'Skift plan',
        'reactivate_subscription' => 'Genaktiver abonnement',
        'cancel_subscription'     => 'Annuller abonnement',

        // Login/logout
        'user_login'  => 'Bruger login',
        'user_logout' => 'Bruger logout',

        // Brugeradministration
        'create' => 'Bruger oprettet',
        'update' => 'Bruger opdateret',
        'delete' => 'Bruger slettet',

        // Organisationsstyring
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
    ],
];