<?php

return [
    'title' => 'Expansion Mapping Console',
    'subtitle' => 'Map TCGCSV groups to RapidAPI expansions',
    'manage_mappings' => 'Manage Mappings',
    'statistics' => 'Statistics',
    'total_groups' => 'Total Groups',
    'mapped_groups' => 'Mapped',
    'unmapped_groups' => 'Unmapped',
    'available_expansions' => 'Available Expansions',
    'mapping_progress' => 'Mapping Progress',
    'tcgcsv_groups' => 'TCGCSV Groups',
    'rapidapi_episodes' => 'RapidAPI Episodes',
    'mapped' => 'Mapped',
    'unmapped' => 'Unmapped',
    'tcgcsv_groups' => 'TCGCSV Groups',
    'rapidapi_episodes' => 'RapidAPI Episodes',
    'mapped' => 'Mapped',
    'unmapped' => 'Unmapped',
    
    'filters' => [
        'label' => 'Filter',
        'all' => 'All Groups',
        'mapped' => 'Mapped Only',
        'unmapped' => 'Unmapped Only',
    ],
    
    'search' => [
        'placeholder' => 'Search by name, code, or ID...',
        'button' => 'Search',
    ],
    
    'table' => [
        'group' => 'TCGCSV Group',
        'group_id' => 'Group ID',
        'abbreviation' => 'Code',
        'published_date' => 'Published',
        'cards_count' => 'Cards',
        'game' => 'Game',
        'rapidapi' => 'RapidAPI Expansion',
        'status' => 'Status',
        'actions' => 'Actions',
        'mapped' => 'Mapped',
        'unmapped' => 'Not Mapped',
    ],
    
    'actions' => [
        'map' => 'Map',
        'unmap' => 'Unmap',
        'select_expansion' => 'Select expansion...',
        'confirm_unmap' => 'Are you sure you want to unmap this group?',
        'suggested_match' => 'Suggested match (same release date)',
        'map_suggested' => 'Map Suggested',
    ],
    
    'messages' => [
        'mapped_success' => 'Successfully mapped :group to :episode',
        'unmapped_success' => 'Successfully unmapped :group from :episode',
        'no_results' => 'No groups found',
        'no_available_expansions' => 'No available expansions to map (all are already assigned)',
    ],
    
    'errors' => [
        'group_not_found' => 'Group not found',
        'group_already_mapped' => 'This group is already mapped to an expansion',
        'group_not_mapped' => 'This group is not mapped to any expansion',
        'rapidapi_not_found' => 'RapidAPI expansion not found',
        'rapidapi_already_assigned' => 'This expansion is already assigned to another group: :group',
    ],
];
