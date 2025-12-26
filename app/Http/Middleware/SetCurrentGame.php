<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Game;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentGame
{
    /**
     * Handle an incoming request and set the current game context.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        
        // Get all available games for this user (enabled in pivot + game active)
        $availableGames = $user->games()
            ->where('games.is_active', true)
            ->where('game_user.is_enabled', true)
            ->orderBy('games.name')
            ->get();

        // Get current game from session
        $currentGameId = session('current_game_id');
        $currentGame = null;

        // Validate current game ID
        if ($currentGameId) {
            $currentGame = $availableGames->firstWhere('id', $currentGameId);
        }

        // If no valid current game, set first available
        if (!$currentGame && $availableGames->isNotEmpty()) {
            $currentGame = $availableGames->first();
            session(['current_game_id' => $currentGame->id]);
        }

        // Share with all views
        view()->share('currentGame', $currentGame);
        view()->share('availableGames', $availableGames);

        // Also make available via request
        $request->attributes->set('currentGame', $currentGame);
        $request->attributes->set('availableGames', $availableGames);

        return $next($request);
    }
}
