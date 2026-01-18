<?php

return [
    'renewal_reminder' => [
        'subject' => 'Dit abonnement fornyes om :days dage',
        'greeting' => 'Hej :name,',
        'intro' => 'Dette er en venlig påmindelse om, at dit abonnement automatisk fornyes om :days dage den :date.',
        'plan_details' => 'Plan: :plan (:period)',
        'amount' => 'Fornyelsesbeløb: :amount :currency',
        'cancel_info' => 'Hvis du ønsker at annullere dit abonnement, bedes du gøre det før fornyelsesdatoen for at undgå gebyrer.',
        'manage_subscription' => 'Administrer Abonnement',
        'thank_you' => 'Tak for din fortsatte støtte!',
    ],    'payment_failed' => [
        'subject' => 'Betaling Mislykkedes - Handling Påkrævet',
        'greeting' => 'Hej :name,',
        'intro' => 'Vi kunne ikke behandle din abonnementsbetaling.',
        'amount' => 'Beløb: :amount :currency',
        'action_required' => 'Opdater venligst din betalingsmetode for at fortsætte dit abonnement.',
        'update_payment' => 'Opdater Betalingsmetode',
        'warning' => 'Hvis betalingen ikke opdateres, kan dit abonnement blive annulleret.',
    ],];
