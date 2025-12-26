<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CurrentGameController extends Controller
{
    /**
     * Switch the current game for the authenticated user.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'game_id' => ['required', 'integer', 'exists:games,id'],
        ]);

        $gameId = $validated['game_id'];

        // Verify user has access to this game
        $hasAccess = $request->user()
            ->games()
            ->where('games.id', $gameId)
            ->where('games.is_active', true)
            ->where('game_user.is_enabled', true)
            ->exists();

        if (!$hasAccess) {
            return back()->with('error', 'You do not have access to this game.');
        }

        // Store in session
        session(['current_game_id' => $gameId]);

        return back()->with('success', 'Game switched successfully.');
    }
}
