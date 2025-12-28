<?php

namespace App\Http\Controllers\Pokemon;

use App\Http\Controllers\Controller;
use App\Services\PokemonDeckValuationService;
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

    public function __construct(PokemonDeckValuationService $service)
    {
        $this->service = $service;
    }

    /**
     * Step 1: Show card selection interface
     */
    public function step1Show(Request $request): View
    {
        // Get or create guest deck
        $sessionUuid = $request->session()->get('valuation_deck_uuid');
        $guestDeck = $this->service->getOrCreateGuestDeck($sessionUuid);
        
        // Store UUID in session
        $request->session()->put('valuation_deck_uuid', $guestDeck->uuid);
        
        // Get items from session (or from guest deck payload)
        $items = $request->session()->get('valuation_items', $guestDeck->payload ?? []);
        
        // Load full card details for items
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

        return view('pokemon.deck_valuation.step1', compact('guestDeck', 'itemsWithDetails'));
    }

    /**
     * Step 1: AJAX search for cards
     */
    public function step1Search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = $this->service->searchCards($query, 10);
        
        return response()->json($results);
    }

    /**
     * Step 1: Add card to deck
     */
    public function step1Add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'qty' => 'nullable|integer|min:1|max:4',
        ]);

        $sessionUuid = $request->session()->get('valuation_deck_uuid');
        $guestDeck = $this->service->getOrCreateGuestDeck($sessionUuid);
        
        $items = $request->session()->get('valuation_items', []);
        $this->service->addCardToSession($items, $validated['product_id'], $validated['qty'] ?? 1);
        
        $request->session()->put('valuation_items', $items);
        $this->service->syncGuestDeckPayload($guestDeck, $items);

        return back()->with('success', __('deckvaluation.success_card_added'));
    }

    /**
     * Step 1: Remove card from deck
     */
    public function step1Remove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
        ]);

        $sessionUuid = $request->session()->get('valuation_deck_uuid');
        $guestDeck = $this->service->getOrCreateGuestDeck($sessionUuid);
        
        $items = $request->session()->get('valuation_items', []);
        $this->service->removeCardFromSession($items, $validated['product_id']);
        
        $request->session()->put('valuation_items', $items);
        $this->service->syncGuestDeckPayload($guestDeck, $items);

        return back()->with('success', __('deckvaluation.success_card_removed'));
    }

    /**
     * Step 2: Show identity capture form
     */
    public function step2Show(Request $request): View|RedirectResponse
    {
        $items = $request->session()->get('valuation_items', []);
        
        if (empty($items)) {
            return redirect()->route('pokemon.deck-valuation.step1')
                ->with('error', __('deckvaluation.error_empty_deck'));
        }

        $sessionUuid = $request->session()->get('valuation_deck_uuid');
        $guestDeck = $this->service->getOrCreateGuestDeck($sessionUuid);

        return view('pokemon.deck_valuation.step2', compact('guestDeck'));
    }

    /**
     * Step 2: Submit identity and create lead
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

        $sessionUuid = $request->session()->get('valuation_deck_uuid');
        $guestDeck = $this->service->getOrCreateGuestDeck($sessionUuid);

        // Create lead and valuation
        $valuation = $this->service->createLeadAndValuation(
            $guestDeck,
            $validated['email'],
            $validated['deck_name'],
            $validated['consent_marketing'] ?? false,
            $items
        );

        // Send email with valuation link
        try {
            \Mail::to($validated['email'])->send(
                new \App\Mail\DeckValuationMail($guestDeck, $valuation, $validated['deck_name'])
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send deck valuation email: ' . $e->getMessage());
        }

        // Clear session items
        $request->session()->forget('valuation_items');

        // Show thank you page
        return view('pokemon.deck_valuation.thank-you', [
            'email' => $validated['email'],
            'deckName' => $validated['deck_name'],
            'guestDeck' => $guestDeck,
            'quickAccessLink' => route('pokemon.deck-valuation.step3', $guestDeck->uuid),
        ]);
    }

    /**
     * Step 3: Show deck valuation results
     */
    public function step3Show(Request $request, string $uuid): View
    {
        $guestDeck = GuestDeck::where('uuid', $uuid)->firstOrFail();
        
        // Check if expired
        if ($guestDeck->expires_at && $guestDeck->expires_at->isPast()) {
            abort(410, __('deckvaluation.error_expired'));
        }
        
        // Get the valuation
        $valuation = $guestDeck->deckValuations()->latest()->first();
        
        if (!$valuation) {
            abort(404, 'Valuation not found');
        }

        // Compute stats
        $stats = $this->service->computeDeckStats($valuation);

        // If user is authenticated, offer to attach
        $canAttach = Auth::check() && !$valuation->user_id;

        return view('pokemon.deck_valuation.step3', compact('guestDeck', 'valuation', 'stats', 'canAttach'));
    }

    /**
     * Attach valuation to authenticated user
     */
    public function attachToUser(Request $request, string $uuid): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Please log in to save your deck valuation.');
        }

        $guestDeck = GuestDeck::where('uuid', $uuid)->firstOrFail();
        $valuation = $guestDeck->deckValuations()->latest()->first();

        if (!$valuation) {
            abort(404, 'Valuation not found');
        }

        $this->service->attachToUser($valuation, Auth::id());

        return back()->with('success', __('deckvaluation.success_valuation_saved'));
    }
}
