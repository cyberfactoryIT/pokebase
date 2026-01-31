<?php

/**
 * Catalog helper functions
 */

if (!function_exists('catalog_backend')) {
    /**
     * Get current catalog backend based on the game
     * Each game has its own catalog backend configured in the database
     */
    function catalog_backend(): string
    {
        // Try to get from current game (authenticated user context)
        $currentGame = request()->attributes->get('currentGame');
        
        if ($currentGame && $currentGame->catalog_backend) {
            return $currentGame->catalog_backend;
        }
        
        // For public Pokemon routes (when user is not authenticated)
        if (request()->is('pokemon/*')) {
            // Get Pokemon game from database
            $pokemonGame = \App\Models\Game::where('code', 'pokemon')->first();
            if ($pokemonGame && $pokemonGame->catalog_backend) {
                return $pokemonGame->catalog_backend;
            }
            return 'tcgdex'; // Fallback for Pokemon
        }
        
        // Default fallback
        return 'tcgcsv';
    }
}

if (!function_exists('is_tcgdex_catalog')) {
    /**
     * Check if TCGDEX catalog is active
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
