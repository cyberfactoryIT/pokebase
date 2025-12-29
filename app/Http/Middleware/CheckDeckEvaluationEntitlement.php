<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\DeckEvaluationEntitlementService;
use Illuminate\Support\Facades\Auth;

class CheckDeckEvaluationEntitlement
{
    private DeckEvaluationEntitlementService $entitlementService;

    public function __construct(DeckEvaluationEntitlementService $entitlementService)
    {
        $this->entitlementService = $entitlementService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Get user/guest identifiers
        $userId = Auth::id();
        $guestToken = $request->cookie('deck_eval_guest_token') 
            ?? $request->session()->get('deck_eval_guest_token');

        // Get cards count from session
        $items = $request->session()->get('valuation_items', []);
        $cardsCount = count($items);

        if ($cardsCount === 0) {
            return $next($request);
        }

        // Check entitlement
        $check = $this->entitlementService->canEvaluate($userId, $guestToken, $cardsCount);

        if (!$check['allowed']) {
            // Store entitlement info in session for display
            $request->session()->put('entitlement_check', $check);

            return redirect()
                ->route('deck-evaluation.packages.index')
                ->with('entitlement_required', $check['reason']);
        }

        // Store check result for use in controller
        $request->attributes->set('entitlement_check', $check);

        return $next($request);
    }
}
