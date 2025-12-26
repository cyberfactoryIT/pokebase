<?php

namespace App\Services;

use App\Models\Game;
use Illuminate\Support\Facades\Auth;

/**
 * Service per gestire il contesto del gioco corrente
 */
class CurrentGameContext
{
    /**
     * Get the current game from session
     */
    public static function get(): ?Game
    {
        if (!Auth::check()) {
            return null;
        }

        $gameId = session('current_game_id');
        
        if (!$gameId) {
            return static::getFirstAvailable();
        }

        return Auth::user()
            ->games()
            ->where('games.id', $gameId)
            ->where('games.is_active', true)
            ->where('game_user.is_enabled', true)
            ->first();
    }

    /**
     * Get the first available game for current user
     */
    public static function getFirstAvailable(): ?Game
    {
        if (!Auth::check()) {
            return null;
        }

        return Auth::user()
            ->games()
            ->where('games.is_active', true)
            ->where('game_user.is_enabled', true)
            ->orderBy('games.name')
            ->first();
    }

    /**
     * Get ID of current game
     */
    public static function id(): ?int
    {
        $game = static::get();
        return $game ? $game->id : null;
    }

    /**
     * Set the current game
     */
    public static function set(int $gameId): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $hasAccess = Auth::user()
            ->games()
            ->where('games.id', $gameId)
            ->where('games.is_active', true)
            ->where('game_user.is_enabled', true)
            ->exists();

        if (!$hasAccess) {
            return false;
        }

        session(['current_game_id' => $gameId]);
        return true;
    }

    /**
     * Clear current game selection
     */
    public static function clear(): void
    {
        session()->forget('current_game_id');
    }

    /**
     * Get all available games for current user
     */
    public static function available()
    {
        if (!Auth::check()) {
            return collect();
        }

        return Auth::user()
            ->games()
            ->where('games.is_active', true)
            ->where('game_user.is_enabled', true)
            ->orderBy('games.name')
            ->get();
    }
}
