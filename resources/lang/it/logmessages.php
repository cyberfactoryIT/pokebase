<?php 
return [

    'type' => [
        'login' => 'Login', 
        'user' => 'Gestione utenti', 
        'billing' => 'Fatturazione',
        'organization' => 'Organizzazione',
        'subscription' => 'Abbonamento',

        
        
    ],
    'action' => [
        // Billing già presente
        'change_plan'             => 'Cambia piano',
        'reactivate_subscription' => 'Riattiva abbonamento',
        'cancel_subscription'     => 'Annulla abbonamento',

        // Login/logout
        'user_login'  => 'Login utente',
        'user_logout' => 'Logout utente',

        // Gestione utenti
        'create' => 'Utente creato',
        'update' => 'Utente aggiornato',
        'delete' => 'Utente eliminato',

        // Gestione organizzazione
        'update_organization' => 'Organizzazione aggiornata',
        
    ],
    'data' => [
        'user' => 'Utente',
        'organization_id' => 'ID organizzazione',
        'organization_name' => 'Nome organizzazione',
        'organization' => 'Nome organizzazione',
        'organization_code' => 'Codice organizzazione',
        'plan' => 'Piano',
        'previous_plan' => 'Piano precedente',
        'new_plan' => 'Nuovo piano',
        'reason' => 'Motivo',
        'cancellation_date' => 'Data di annullamento',
        'reactivation_date' => 'Data di riattivazione',
        'subscription_end_date' => 'Data fine abbonamento',
        'subscription_renewal_date' => 'Data rinnovo abbonamento',
        'date' => 'Data',
        'promotion_code' => 'Codice promozione',
        'promotion_end_date' => 'Data fine promozione',
        'previous_promotion_code' => 'Codice promozione precedente',
        'previous_promotion_end_date' => 'Data fine promozione precedente',
        'period' => 'Periodo di fatturazione',
        'coupon_code' => 'Codice coupon',
        'new_plan_id' => 'ID nuovo piano',
        'old_plan_id' => 'ID piano precedente',
        'discount_cents' => 'Importo sconto (centesimi)',
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