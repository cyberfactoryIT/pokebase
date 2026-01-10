<?php

return [
    'title' => 'Console Mappatura Espansioni',
    'subtitle' => 'Mappa i gruppi TCGCSV alle espansioni RapidAPI',
    'statistics' => 'Statistiche',
    'total_groups' => 'Gruppi Totali',
    'mapped_groups' => 'Mappati',
    'unmapped_groups' => 'Non Mappati',
    'available_expansions' => 'Espansioni Disponibili',
    'mapping_progress' => 'Progresso Mappatura',
    
    'filters' => [
        'label' => 'Filtro',
        'all' => 'Tutti i Gruppi',
        'mapped' => 'Solo Mappati',
        'unmapped' => 'Solo Non Mappati',
    ],
    
    'search' => [
        'placeholder' => 'Cerca per nome, codice o ID...',
        'button' => 'Cerca',
    ],
    
    'table' => [
        'group' => 'Gruppo TCGCSV',
        'group_id' => 'ID Gruppo',
        'abbreviation' => 'Codice',
        'published_date' => 'Pubblicato',
        'game' => 'Gioco',
        'rapidapi' => 'Espansione RapidAPI',
        'status' => 'Stato',
        'actions' => 'Azioni',
        'mapped' => 'Mappato',
        'unmapped' => 'Non Mappato',
    ],
    
    'actions' => [
        'map' => 'Mappa',
        'unmap' => 'Rimuovi',
        'select_expansion' => 'Seleziona espansione...',
        'confirm_unmap' => 'Sei sicuro di voler rimuovere la mappatura di questo gruppo?',
        'suggested_match' => 'Match suggerito (stessa data di rilascio)',
        'map_suggested' => 'Mappa Suggerito',
    ],
    
    'messages' => [
        'mapped_success' => 'Mappato con successo :group a :episode',
        'unmapped_success' => 'Rimosso con successo :group da :episode',
        'no_results' => 'Nessun gruppo trovato',
        'no_available_expansions' => 'Nessuna espansione disponibile da mappare (tutte già assegnate)',
    ],
    
    'errors' => [
        'group_not_found' => 'Gruppo non trovato',
        'group_already_mapped' => 'Questo gruppo è già mappato ad un\'espansione',
        'group_not_mapped' => 'Questo gruppo non è mappato ad alcuna espansione',
        'rapidapi_not_found' => 'Espansione RapidAPI non trovata',
        'rapidapi_already_assigned' => 'Questa espansione è già assegnata ad un altro gruppo: :group',
    ],
];
