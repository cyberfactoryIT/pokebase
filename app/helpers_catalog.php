<?php

/**
 * Catalog helper functions
 */

if (!function_exists('catalog_backend')) {
    /**
     * Get current catalog backend (only TCGDEX for Pokemon, TCGCSV for everything else)
     */
    function catalog_backend(): string
    {
        $backend = config('catalog.backend', 'tcgcsv');
        
        // TCGDEX is ONLY available for specific games
        if ($backend === 'tcgdex') {
            $currentGame = request()->attributes->get('currentGame');
            
            // If no game is set, we're likely in a public route context
            // In Pokemon routes (e.g., /pokemon/*), we can assume Pokemon game
            if (!$currentGame) {
                // Check if we're in a Pokemon route
                if (request()->is('pokemon/*')) {
                    return 'tcgdex'; // Allow TCGDEX for Pokemon routes
                }
                return 'tcgcsv'; // Fallback for other routes
            }
            
            $supportedGames = config('catalog.tcgdex_supported_games', [1]);
            
            // If game not in supported list, fallback to TCGCSV
            if (!in_array($currentGame->id, $supportedGames)) {
                return 'tcgcsv';
            }
        }
        
        return $backend;
    }
}

if (!function_exists('is_tcgdex_catalog')) {
    /**
     * Check if TCGDEX catalog is active (only for Pokemon)
     */
    function is_tcgdex_catalog(): bool
    {
        return catalog_backend() === 'tcgdex';
    }
}

if (!function_exists('is_tcgcsv_catalog')) {
    /**
     * Check if TCGCSV catalog is active
     */
    function is_tcgcsv_catalog(): bool
    {
        return catalog_backend() === 'tcgcsv';
    }
}

if (!function_exists('is_pokemon_game')) {
    /**
     * Check if current game is Pokemon
     */
    function is_pokemon_game(): bool
    {
        $currentGame = request()->attributes->get('currentGame');
        return $currentGame && $currentGame->id === 1;
    }
}
