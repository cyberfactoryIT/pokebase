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

        $deck->load([
            'deckCards.card.group',
            'deckCards.card.prices' => function($query) {
                $query->latest('snapshot_at')->limit(1);
            },
            'deckCards.card.rapidapiCard'
        ]);

        // Calculate deck statistics
        $stats = $this->getDeckStats($deck);
        $topStats = $this->getDeckTopStats($deck);

        return view('decks.show', compact('deck', 'stats', 'topStats'));
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

    /**
     * Get basic deck statistics
     */
    private function getDeckStats(Deck $deck): array
    {
        $totalCards = $deck->deckCards->sum('quantity');
        $uniqueCards = $deck->deckCards->count();
        
        return [
            'total_cards' => $totalCards,
            'unique_cards' => $uniqueCards,
        ];
    }

    /**
     * Get top deck statistics for display
     */
    private function getDeckTopStats(Deck $deck): array
    {
        // 1. Rarity distribution
        $rarityDistribution = $deck->deckCards
            ->groupBy(fn($dc) => $dc->card->rarity ?? 'Unknown')
            ->map(fn($group) => [
                'count' => $group->count(),
                'total_quantity' => $group->sum('quantity')
            ])
            ->sortByDesc('count');

        // 2. Set distribution
        $setDistribution = $deck->deckCards
            ->groupBy(fn($dc) => $dc->card->group->name ?? 'Unknown')
            ->map(fn($group) => [
                'set_name' => $group->first()->card->group->name ?? 'Unknown',
                'count' => $group->count(),
                'total_quantity' => $group->sum('quantity')
            ])
            ->sortByDesc('count')
            ->take(5);

        // 3. Card values (USD from TCGPlayer, EUR from Cardmarket/RapidAPI)
        $totalValueUsd = 0;
        $totalValueEur = 0;
        $cardsWithPricesUsd = 0;
        $cardsWithPricesEur = 0;
        
        foreach ($deck->deckCards as $deckCard) {
            // USD price from TCGPlayer
            $latestPrice = $deckCard->card->prices->first();
            if ($latestPrice && $latestPrice->market_price > 0) {
                $totalValueUsd += $latestPrice->market_price * $deckCard->quantity;
                $cardsWithPricesUsd++;
            }
            
            // EUR price from RapidAPI Cardmarket data
            $rapidapiCard = $deckCard->card->rapidapiCard;
            if ($rapidapiCard && isset($rapidapiCard->raw_data['prices']['cardmarket']['lowest_near_mint'])) {
                $marketPriceEur = (float) $rapidapiCard->raw_data['prices']['cardmarket']['lowest_near_mint'];
                if ($marketPriceEur > 0) {
                    $totalValueEur += $marketPriceEur * $deckCard->quantity;
                    $cardsWithPricesEur++;
                }
            }
        }

        return [
            'rarity_distribution' => $rarityDistribution,
            'set_distribution' => $setDistribution,
            'total_value_usd' => round($totalValueUsd, 2),
            'total_value_eur' => round($totalValueEur, 2),
            'cards_with_prices_usd' => $cardsWithPricesUsd,
            'cards_with_prices_eur' => $cardsWithPricesEur,
        ];
    }
}
