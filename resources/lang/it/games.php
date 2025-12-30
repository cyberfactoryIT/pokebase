<?php

return [
    // Game activation limits
    'limit' => [
        'reached' => [
            'title' => 'Limite Giochi Raggiunto',
            'body' => 'Il tuo abbonamento :tier consente :max gioco/i attivo/i. Disattiva un gioco o aggiorna il tuo piano per attivarne altri.',
            'body_at_limit' => 'Il tuo abbonamento :tier consente :max gioco/i attivo/i. Hai raggiunto il limite.',
        ],
        'cta_upgrade' => 'Aggiorna a Premium',
        'upgrade_benefits' => 'Aggiorna ad Advanced (3 giochi) o Premium (giochi illimitati)',
    ],
    
    // Activation messages
    'activation' => [
        'success' => 'Gioco attivato con successo!',
        'not_allowed' => 'Non puoi attivare questo gioco. Limite raggiunto per il tuo livello di abbonamento.',
        'must_have_one' => 'Devi avere almeno un gioco attivo.',
    ],
    
    // Usage messages
    'usage' => [
        'not_active' => 'Questo gioco non è attivo per il tuo account.',
        'activate_first' => 'Per favore attiva questo gioco nel tuo profilo per utilizzarlo.',
        'or_upgrade' => 'Oppure aggiorna il tuo abbonamento per attivare più giochi.',
    ],
    
    // Tier display
    'tier' => [
        'free' => 'Gratuito',
        'advanced' => 'Advanced',
        'premium' => 'Premium',
    ],
    
    // Limits display
    'limits' => [
        'free' => '1 gioco',
        'advanced' => '3 giochi',
        'premium' => 'Giochi illimitati',
    ],
];
