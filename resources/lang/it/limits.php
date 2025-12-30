<?php

return [
    // Card limits
    'cards' => [
        'free' => [
            'limit' => '100 carte',
            'usage' => ':used di :limit carte utilizzate',
            'remaining' => ':remaining slot carte rimanenti',
            'unlimited' => 'Carte illimitate',
        ],
        'reached' => [
            'title' => 'Limite Carte Raggiunto',
            'body' => 'Il tuo abbonamento Gratuito consente fino a :limit carte totali (collezione + mazzi). Attualmente hai :used carte.',
            'body_adding' => 'L\'aggiunta di :amount carta/e supererebbe il tuo limite di :limit carte. Attualmente hai :used carte.',
        ],
        'cta_upgrade' => 'Aggiorna ad Advanced o Premium',
        'upgrade_benefits' => 'Gli abbonamenti Advanced e Premium includono carte illimitate nella tua collezione e nei tuoi mazzi.',
    ],
];
