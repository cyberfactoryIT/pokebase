<?php

/**
 * INTEGRATION GUIDE: Update DeckValuationFlowController
 * 
 * This file shows the changes needed to integrate entitlement checking
 * into the existing DeckValuationFlowController.
 * 
 * DO NOT replace the whole controller - only add the entitlement checks
 * where indicated.
 */

namespace App\Http\Controllers\Pokemon;

use App\Http\Controllers\Controller;
use App\Services\PokemonDeckValuationService;
use App\Services\DeckEvaluationEntitlementService; // ADD THIS
use App\Models\GuestDeck;
use App\Models\DeckValuation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DeckValuationFlowController extends Controller
{
    private PokemonDeckValuationService $service;
    private DeckEvaluationEntitlementService $entitlementService; // ADD THIS

    public function __construct(
        PokemonDeckValuationService $service,
        DeckEvaluationEntitlementService $entitlementService // ADD THIS
    ) {
        $this->service = $service;
        $this->entitlementService = $entitlementService; // ADD THIS
    }

    /**
     * Step 1: Show card selection interface
     * 
     * ADD ENTITLEMENT INFO TO VIEW
     */
    public function step1Show(Request $request): View
    {
        // Get or create guest deck (existing code)
        $sessionUuid = $request->session()->get('valuation_deck_uuid');
        $guestDeck = $this->service->getOrCreateGuestDeck($sessionUuid);
        $request->session()->put('valuation_deck_uuid', $guestDeck->uuid);
        
        // Get items from session
        $items = $request->session()->get('valuation_items', $guestDeck->payload ?? []);
        
        // Load full card details
        $itemsWithDetails = collect($items)->map(function($item) {
            $card = \App\Models\TcgcsvProduct::with('group')
                ->where('product_id', $item['product_id'])
                ->first();
            
            return $card ? [
                'card' => $card,
                'qty' => $item['qty'],
                'product_id' => $item['product_id'],
            ] : null;
        })->filter();

        // ADD ENTITLEMENT CHECK
        $userId = Auth::id();
        $guestToken = $request->cookie('deck_eval_guest_token') 
            ?? $request->session()->get('deck_eval_guest_token');
        
        $entitlementSummary = $this->entitlementService->getEntitlementSummary($userId, $guestToken);
        
        return view('pokemon.deck_valuation.step1', compact('guestDeck', 'itemsWithDetails', 'entitlementSummary'));
    }

    /**
     * Step 2: Submit identity and create lead
     * 
     * ADD ENTITLEMENT CHECK BEFORE PROCESSING
     */
    public function step2Submit(Request $request): View
    {
        $validated = $request->validate([
            'deck_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'consent_marketing' => 'nullable|boolean',
        ]);

        $items = $request->session()->get('valuation_items', []);
        
        if (empty($items)) {
            return redirect()->route('pokemon.deck-valuation.step1')
                ->with('error', 'Your deck is empty.');
        }

        // ADD ENTITLEMENT CHECK
        $userId = Auth::id();
        $guestToken = $request->cookie('deck_eval_guest_token') 
            ?? $request->session()->get('deck_eval_guest_token')
            ?? \App\Models\DeckEvaluationSession::generateGuestToken();
        
        $cardsCount = array_sum(array_column($items, 'qty'));
        $check = $this->entitlementService->canEvaluate($userId, $guestToken, $cardsCount);
        
        if (!$check['allowed']) {
            return redirect()->route('deck-evaluation.packages.index')
                ->with('entitlement_required', $check['reason'])
                ->with('entitlement_check', $check);
        }
        
        // Store guest token if not authenticated
        if (!$userId) {
            $request->session()->put('deck_eval_guest_token', $guestToken);
            cookie()->queue('deck_eval_guest_token', $guestToken, 60 * 24 * 365);
        }

        $sessionUuid = $request->session()->get('valuation_deck_uuid');
        $guestDeck = $this->service->getOrCreateGuestDeck($sessionUuid);

        // Create lead and valuation (existing code)
        $valuation = $this->service->createLeadAndValuation(
            $guestDeck,
            $validated['email'],
            $validated['deck_name'],
            $validated['consent_marketing'] ?? false,
            $items
        );

        // RECORD EVALUATION FOR ENTITLEMENT TRACKING
        $cardProductIds = array_column($items, 'product_id');
        $this->entitlementService->recordEvaluation(
            $check['session'],
            $cardProductIds,
            $check['purchase'] ?? null
        );

        // Send email (existing code)
        try {
            \Mail::to($validated['email'])->send(
                new \App\Mail\DeckValuationMail($guestDeck, $valuation, $validated['deck_name'])
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send deck valuation email: ' . $e->getMessage());
        }

        // Clear session items
        $request->session()->forget('valuation_items');

        return view('pokemon.deck_valuation.thank-you', [
            'email' => $validated['email'],
            'deckName' => $validated['deck_name'],
            'guestDeck' => $guestDeck,
            'quickAccessLink' => route('pokemon.deck-valuation.step3', $guestDeck->uuid),
        ]);
    }

    /**
     * Step 3: Show deck valuation results
     * 
     * ADD ENTITLEMENT VERIFICATION
     */
    public function step3Show(Request $request, string $uuid): View
    {
        $guestDeck = GuestDeck::where('uuid', $uuid)->firstOrFail();
        
        // Check if expired (existing code)
        if ($guestDeck->expires_at && $guestDeck->expires_at->isPast()) {
            abort(410, __('deckvaluation.error_expired'));
        }
        
        // ADD: Verify entitlement to view results
        $userId = Auth::id();
        $guestToken = $request->cookie('deck_eval_guest_token') 
            ?? $request->session()->get('deck_eval_guest_token');
        
        $activePurchase = $this->entitlementService->getActivePurchase($userId, $guestToken);
        
        // If no active purchase and not free tier, block access
        if (!$activePurchase) {
            $session = $this->entitlementService->getOrCreateSession($userId, $guestToken);
            if (!$session->hasFreeCardsRemaining()) {
                return redirect()->route('deck-evaluation.packages.index')
                    ->with('error', __('deck_evaluation.entitlement.expired'));
            }
        }
        
        // Get the valuation (existing code)
        $valuation = $guestDeck->deckValuations()->latest()->first();
        
        if (!$valuation) {
            abort(404, 'Valuation not found');
        }

        // Compute stats (existing code)
        $stats = $this->service->computeDeckStats($valuation);

        // If user is authenticated, offer to attach
        $canAttach = Auth::check() && !$valuation->user_id;

        return view('pokemon.deck_valuation.step3', compact('guestDeck', 'valuation', 'stats', 'canAttach'));
    }

    // Other methods remain unchanged...
}

/**
 * BLADE VIEW UPDATES
 * 
 * In pokemon/deck_valuation/step1.blade.php, add entitlement display:
 */
/*
@if(isset($entitlementSummary))
    <div class="mb-6 p-4 bg-blue-900/30 border border-blue-500/50 rounded-lg">
        @if($entitlementSummary['type'] === 'free')
            <div class="flex items-center justify-between text-white">
                <div>
                    <div class="font-semibold">{{ __('deck_evaluation.entitlement.free_limit', [
                        'used' => $entitlementSummary['cards_used'],
                        'limit' => $entitlementSummary['cards_limit']
                    ]) }}</div>
                    <div class="text-sm text-gray-400">{{ __('deck_evaluation.packages.free_tier') }}</div>
                </div>
                <a href="{{ route('deck-evaluation.packages.index') }}" 
                   class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition text-sm">
                    {{ __('deck_evaluation.packages.title') }}
                </a>
            </div>
        @else
            <div class="flex items-center justify-between text-white">
                <div>
                    <div class="font-semibold">{{ $entitlementSummary['package_name'] }}</div>
                    <div class="text-sm text-gray-400">
                        @if($entitlementSummary['is_unlimited'])
                            {{ __('deck_evaluation.entitlement.unlimited_remaining', [
                                'date' => $entitlementSummary['expires_at']->format('Y-m-d')
                            ]) }}
                        @else
                            {{ __('deck_evaluation.entitlement.purchased_limit', [
                                'used' => $entitlementSummary['cards_used'],
                                'limit' => $entitlementSummary['cards_limit']
                            ]) }}
                        @endif
                    </div>
                </div>
                <span class="px-3 py-1 bg-emerald-900/50 text-emerald-400 border border-emerald-500 rounded-full text-xs font-semibold">
                    {{ __('deck_evaluation.account.status_active') }}
                </span>
            </div>
        @endif
    </div>
@endif
*/
