<?php

use App\Services\CurrentGameContext;

if (!function_exists('currentGame')) {
    /**
     * Get the current game
     */
    function currentGame(): ?\App\Models\Game
    {
        return CurrentGameContext::get();
    }
}

if (!function_exists('currentGameId')) {
    /**
     * Get the current game ID
     */
    function currentGameId(): ?int
    {
        return CurrentGameContext::id();
    }
}

if (!function_exists('availableGames')) {
    /**
     * Get all available games for current user
     */
    function availableGames()
    {
        return CurrentGameContext::available();
    }
}
