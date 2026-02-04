<?php

return [
    // Billing Page
    'billing_title' => 'Fakturering og Abonnement',
    'billing_subtitle' => 'Administrer dit abonnement, se fakturaer og købshistorik',
    
    // Invoices Section
    'invoices' => [
        'title' => 'Fakturaer',
        'no_invoices' => 'Ingen fakturaer endnu',
    ],
    
    // Membership Section
    'membership' => [
        'title' => 'Medlemskab (Gentagen)',
        'current_plan' => 'Nuværende Plan',
        'status' => 'Status',
        'billing_period' => 'Faktureringsperiode',
        'next_renewal' => 'Næste Fornyelse',
        'renews_today' => 'Fornyes i dag',
        'renews_tomorrow' => 'Fornyes i morgen',
        'renews_in_days' => 'Fornyes om :days dage',
        'no_active_membership' => 'Intet aktivt medlemskab',
        'explanation' => 'Dit medlemskabsabonnement giver adgang til premium-funktioner med gentagen fakturering.',
        
        // Status values
        'status_active' => 'Aktiv',
        'status_cancelled' => 'Annulleret',
        'status_past_due' => 'Forsinket',
        'status_expired' => 'Udløbet',
        
        // Billing periods
        'monthly' => 'Månedlig',
        'yearly' => 'Årlig',
        
        // Actions
        'change_plan' => 'Skift Plan',
        'cancel_subscription' => 'Annuller Abonnement',
        'reactivate_subscription' => 'Genaktiver Abonnement',
    ],

    // Deck Evaluation Section
    'deck_evaluation' => [
        'title' => 'Deck Evaluering (Engangs Køb)',
        'active_purchases' => 'Aktive Køb',
        'expired_purchases' => 'Udløbne Køb',
        'no_purchases' => 'Ingen deck evalueringskøb',
        'explanation' => 'Deck evalueringspakker er separate engangskøb, der giver dig mulighed for at evaluere dine kortdeck.',
        'coexistence_note' => 'Bemærk: Medlemskab og Deck Evaluering er separate produkter. Du kan have begge aktive samtidigt.',
        
        'package_name' => 'Pakke',
        'valid_until' => 'Gyldig Indtil',
        'cards_used' => 'Kort Brugt',
        'cards_limit' => 'Kort Grænse',
        'unlimited_package' => 'Ubegrænset',
        'multiple_decks_allowed' => 'Flere deck tilladt',
        
        'go_to_deck_evaluation' => 'Gå til Deck Evaluering',
        'purchase_package' => 'Køb Pakke',        
        // Advanced/Premium messaging
        'included_in_plan' => 'Dæk Evaluering Inkluderet',
        'advanced_premium_note' => 'Din Advanced/Premium plan inkluderer ubegrænsede dæk evalueringer. Du kan evaluere så mange dæk, som du vil, uden at købe yderligere pakker.',
        'start_evaluation' => 'Start Dæk Evaluering',    ],

    // Plan Tiers
    'tiers' => [
        'free' => 'Gratis',
        'advanced' => 'Avanceret',
        'premium' => 'Premium',
    ],

    // Sektion Tilgængelige Planer
    'available_plans' => 'Tilgængelige Planer',
    'available_plans_subtitle' => 'Vælg den plan, der passer til dine behov',

    // Faktureringsinformation
    'billing_info' => [
        'title' => 'Faktureringsinformation',
        'subtitle' => 'Administrer dine faktureringsoplysninger og firmaoplysninger',
        'edit' => 'Rediger',
        'company' => 'Navn eller Firma',
        'billing_email' => 'Fakturerings E-mail',
        'vat' => 'Momsnummer',
        'country' => 'Land',
        'select_country' => 'Vælg et land',
        'address' => 'Adresse',
        'city' => 'By',
        'postcode' => 'Postnummer',
        'save' => 'Gem Ændringer',
        'cancel' => 'Annuller',
        'no_org' => 'Ingen Organisation',
        'no_org_desc' => 'Ingen organisation tilknyttet din konto',
    ],
];
