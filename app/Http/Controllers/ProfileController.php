<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\TransactionHistoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $allGames = \App\Models\Game::where('is_active', true)->orderBy('name')->get();
        $userGames = $request->user()->games()->pluck('games.id')->toArray();

        return view('profile.edit', [
            'user' => $request->user(),
            'allGames' => $allGames,
            'userGames' => $userGames,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Aggiorna la password solo se presente nel request
        if ($request->filled('password')) {
            $request->user()->password = bcrypt($request->input('password'));
        }

        $request->user()->save();

        // Revoke all remember tokens after profile update (e.g. password/email change)
        app(\App\Services\RememberTokenService::class)->revokeAll($request->user());

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
    // Revoke all remember tokens on logout-all
    app(\App\Services\RememberTokenService::class)->revokeAll($request->user());

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Update the user's theme preference.
     */
    public function updateTheme(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in(['dark', 'light', 'pokemon', 'pokemon-light', 'gameboy'])],
        ]);

        $user = $request->user();
        $user->theme = $validated['theme'];
        $user->save();

        return response()->json([
            'success' => true,
            'theme' => $validated['theme'],
        ]);
    }

    /**
     * Update the user's game preferences.
     */
    public function updateGames(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'games' => ['nullable', 'array'],
            'games.*' => ['exists:games,id'],
            'default_game_id' => ['nullable', 'exists:games,id'],
        ]);

        $gameIds = $validated['games'] ?? [];
        $request->user()->games()->sync($gameIds);

        // Update default game only if it's provided and it's among the selected games
        if (isset($validated['default_game_id'])) {
            if (in_array($validated['default_game_id'], $gameIds)) {
                $request->user()->default_game_id = $validated['default_game_id'];
            } else {
                // If default game is not in selected games, clear it
                $request->user()->default_game_id = null;
            }
        } elseif (empty($gameIds)) {
            // If no games selected, clear default game
            $request->user()->default_game_id = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'games-updated');
    }

    /**
     * Display the user's subscription tab.
     */
    public function subscription(Request $request): View
    {
        $user = $request->user();
        $membershipStatus = $user->membershipStatus();
        
        // Get active and expired deck evaluation purchases
        $activePurchases = $user->deckEvaluationPurchases()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->with('package')
            ->orderBy('expires_at', 'desc')
            ->get();
            
        $expiredPurchases = $user->deckEvaluationPurchases()
            ->where(function($query) {
                $query->where('status', '!=', 'active')
                    ->orWhere('expires_at', '<=', now());
            })
            ->with('package')
            ->orderBy('purchased_at', 'desc')
            ->limit(5)
            ->get();

        return view('profile.subscription', compact(
            'user',
            'membershipStatus',
            'activePurchases',
            'expiredPurchases'
        ));
    }

    /**
     * Display the user's transaction history.
     */
    public function transactions(Request $request, TransactionHistoryService $historyService): View
    {
        $user = $request->user();
        $transactions = $historyService->getHistory($user);

        return view('profile.transactions', compact('user', 'transactions', 'historyService'));
    }
}
