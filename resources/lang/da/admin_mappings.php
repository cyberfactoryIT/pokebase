<?php

return [
    'title' => 'Udvidelsesmappingskonsol',
    'subtitle' => 'Map TCGCSV-grupper til RapidAPI-udvidelser',
    'statistics' => 'Statistik',
    'total_groups' => 'Samlede Grupper',
    'mapped_groups' => 'Mappede',
    'unmapped_groups' => 'Ikke Mappede',
    'available_expansions' => 'Tilgængelige Udvidelser',
    'mapping_progress' => 'Mapping Fremskridt',
    
    'filters' => [
        'label' => 'Filter',
        'all' => 'Alle Grupper',
        'mapped' => 'Kun Mappede',
        'unmapped' => 'Kun Ikke Mappede',
    ],
    
    'search' => [
        'placeholder' => 'Søg efter navn, kode eller ID...',
        'button' => 'Søg',
    ],
    
    'table' => [
        'group' => 'TCGCSV Gruppe',
        'group_id' => 'Gruppe ID',
        'abbreviation' => 'Kode',
        'published_date' => 'Udgivet',
        'game' => 'Spil',
        'rapidapi' => 'RapidAPI Udvidelse',
        'status' => 'Status',
        'actions' => 'Handlinger',
        'mapped' => 'Mappet',
        'unmapped' => 'Ikke Mappet',
    ],
    
    'actions' => [
        'map' => 'Map',
        'unmap' => 'Fjern',
        'select_expansion' => 'Vælg udvidelse...',
        'confirm_unmap' => 'Er du sikker på, at du vil fjerne mappingen for denne gruppe?',
        'suggested_match' => 'Foreslået match (samme udgivelsesdato)',
        'map_suggested' => 'Map Foreslået',
    ],
    
    'messages' => [
        'mapped_success' => 'Mappet :group til :episode med succes',
        'unmapped_success' => 'Fjernede mapping for :group fra :episode med succes',
        'no_results' => 'Ingen grupper fundet',
        'no_available_expansions' => 'Ingen tilgængelige udvidelser at mappe (alle er allerede tildelt)',
    ],
    
    'errors' => [
        'group_not_found' => 'Gruppe ikke fundet',
        'group_already_mapped' => 'Denne gruppe er allerede mappet til en udvidelse',
        'group_not_mapped' => 'Denne gruppe er ikke mappet til nogen udvidelse',
        'rapidapi_not_found' => 'RapidAPI udvidelse ikke fundet',
        'rapidapi_already_assigned' => 'Denne udvidelse er allerede tildelt en anden gruppe: :group',
    ],
];
