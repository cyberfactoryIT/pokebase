<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\TransactionHistoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
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
        
        // Force refresh organization relationship to get latest data
        $user->load('organization.pricingPlan');
        
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
     * Display the user's transactions tab.
     */
    public function transactions(Request $request): View
    {
        $user = $request->user();
        
        // Get transaction history using the service
        $transactionService = app(TransactionHistoryService::class);
        $transactions = $transactionService->getHistory($user);
        
        return view('profile.transactions', compact(
            'user',
            'transactions'
        ));
    }

    /**
     * TEST ONLY: Quick switch pricing plan
     */
    public function testSwitchPlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:pricing_plans,id',
        ]);

        $user = $request->user();
        
        // Get the plan details
        $plan = \App\Models\PricingPlan::findOrFail($validated['plan_id']);
        
        // Get or create organization if needed
        if (!$user->organization) {
            $org = \App\Models\Organization::create([
                'name' => $user->name . "'s Organization",
                'code' => 'ORG-' . strtoupper(Str::random(6)),
                'slug' => Str::slug($user->name) . '-' . time(),
            ]);
            $user->organization_id = $org->id;
            $user->save();
            
            \Log::info('Created new organization for user', [
                'user_id' => $user->id,
                'org_id' => $org->id,
            ]);
        } else {
            $org = $user->organization;
        }

        // Store old plan for logging
        $oldPlanId = $org->pricing_plan_id;

        // Update organization with new plan
        $org->pricing_plan_id = $validated['plan_id'];
        $org->billing_period = 'monthly';
        $org->subscription_date = now();
        $org->renew_date = now()->addMonth();
        $org->subscription_cancelled = 0;
        $org->save();
        
        // Clear relationship cache to ensure fresh data on next request
        $user->unsetRelation('organization');
        $user->load('organization.pricingPlan');
        
        \Log::info('TEST: Plan changed', [
            'user_id' => $user->id,
            'org_id' => $org->id,
            'old_plan_id' => $oldPlanId,
            'new_plan_id' => $validated['plan_id'],
            'plan_name' => $plan->name,
        ]);

        return redirect()
            ->route('profile.subscription')
            ->with('success', "✅ Piano '{$plan->name}' attivato con successo! (TEST MODE)");
    }
}