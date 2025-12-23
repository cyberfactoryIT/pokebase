<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use App\Models\DeckCard;
use App\Models\TcgcsvProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DeckController extends Controller
{
    /**
     * Display a listing of user's decks
     */
    public function index(): View
    {
        $decks = Deck::where('user_id', Auth::id())
            ->with('deckCards')
            ->latest()
            ->get();

        return view('decks.index', compact('decks'));
    }

    /**
     * Show the form for creating a new deck
     */
    public function create(): View
    {
        return view('decks.create');
    }

    /**
     * Store a newly created deck
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'format' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // For now, default to Pokémon game (ID 1)
        $deck = Deck::create([
            'user_id' => Auth::id(),
            'game_id' => 1,
            'name' => $validated['name'],
            'format' => $validated['format'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('decks.show', $deck)
            ->with('success', 'Deck created successfully!');
    }

    /**
     * Display the specified deck
     */
    public function show(Deck $deck): View
    {
        // Authorization check
        if ($deck->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this deck.');
        }

        $deck->load(['deckCards.card']);

        return view('decks.show', compact('deck'));
    }

    /**
     * Show the form for editing the specified deck
     */
    public function edit(Deck $deck): View
    {
        // Authorization check
        if ($deck->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this deck.');
        }

        return view('decks.edit', compact('deck'));
    }

    /**
     * Update the specified deck
     */
    public function update(Request $request, Deck $deck): RedirectResponse
    {
        // Authorization check
        if ($deck->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this deck.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'format' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $deck->update($validated);

        return redirect()->route('decks.show', $deck)
            ->with('success', 'Deck updated successfully!');
    }

    /**
     * Remove the specified deck
     */
    public function destroy(Deck $deck): RedirectResponse
    {
        // Authorization check
        if ($deck->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this deck.');
        }

        $deckName = $deck->name;
        $deck->delete();

        return redirect()->route('decks.index')
            ->with('success', "Deck '{$deckName}' deleted successfully!");
    }

    /**
     * Add a card to the deck
     */
    public function addCard(Request $request, Deck $deck): RedirectResponse
    {
        // Authorization check
        if ($deck->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this deck.');
        }

        $validated = $request->validate([
            'product_id' => 'required|integer|exists:tcgcsv_products,product_id',
            'quantity' => 'nullable|integer|min:1|max:4',
        ]);
        
        // Check if card already in deck
        $existingCard = DeckCard::where('deck_id', $deck->id)
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existingCard) {
            $existingCard->increment('quantity', $validated['quantity'] ?? 1);
            $message = 'Card quantity updated in deck!';
        } else {
            DeckCard::create([
                'deck_id' => $deck->id,
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'] ?? 1,
            ]);
            $message = 'Card added to deck!';
        }

        return back()->with('success', $message);
    }

    /**
     * Remove a card from the deck
     */
    public function removeCard(Deck $deck, DeckCard $deckCard): RedirectResponse
    {
        // Authorization check
        if ($deck->user_id !== Auth::id() || $deckCard->deck_id !== $deck->id) {
            abort(403, 'Unauthorized action.');
        }

        $deckCard->delete();

        return back()->with('success', 'Card removed from deck!');
    }

    /**
     * Update card quantity in deck
     */
    public function updateCardQuantity(Request $request, Deck $deck, DeckCard $deckCard): RedirectResponse
    {
        // Authorization check
        if ($deck->user_id !== Auth::id() || $deckCard->deck_id !== $deck->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:4',
        ]);

        $deckCard->update(['quantity' => $validated['quantity']]);

        return back()->with('success', 'Card quantity updated!');
    }
}
