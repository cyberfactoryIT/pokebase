<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use App\Models\DeckCard;
use App\Models\TcgcsvProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DeckController extends Controller
{
    /**
     * Display a listing of user's decks
     */
    public function index(): View
    {
        $catalogBackend = catalog_backend();
        
        $decks = Deck::where('user_id', Auth::id())
            ->with(['deckCards' => function($query) use ($catalogBackend) {
                // Filter by catalog backend: only show cards from current backend
                if ($catalogBackend === 'tcgdex') {
                    $query->whereNotNull('tcgdex_card_id');
                } else {
                    $query->whereNotNull('product_id');
                }
            }])
            ->latest()
            ->get();

        return view('decks.index', compact('decks'));
    }

    /**
     * Show the form for creating a new deck
     */
    public function create(): View
    {
        // Check if user can create another deck
        if (!Auth::user()->canCreateAnotherDeck()) {
            return redirect()->route('decks.index')
                ->with('error', __('decks/index.limit_reached'));
        }

        return view('decks.create');
    }

    /**
     * Store a newly created deck
     */
    public function store(Request $request): RedirectResponse
    {
        // Check if user can create another deck
        if (!Auth::user()->canCreateAnotherDeck()) {
            return redirect()->route('decks.index')
                ->with('error', __('decks/index.limit_reached'));
        }

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

        $catalogBackend = catalog_backend();

        $deck->load([
            'deckCards' => function($query) use ($catalogBackend) {
                // Filter by catalog backend: only show cards from current backend
                if ($catalogBackend === 'tcgdex') {
                    $query->whereNotNull('tcgdex_card_id');
                } else {
                    $query->whereNotNull('product_id');
                }
            },
            'deckCards.photos',
            'deckCards.card.group',
            'deckCards.card.prices' => function($query) {
                $query->latest('snapshot_at')->limit(1);
            },
            'deckCards.card.rapidapiCard',
            'deckCards.card.cardmarketProduct.latestPriceQuote'
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

        $user = Auth::user();
        $quantityToAdd = $validated['quantity'] ?? 1;
        
        // Check if card already in deck - if so, we only add the difference
        $existingCard = DeckCard::where('deck_id', $deck->id)
            ->where('product_id', $validated['product_id'])
            ->first();

        $actualAmountToAdd = $quantityToAdd;
        if ($existingCard) {
            // We're incrementing, so only count the new quantity
            $actualAmountToAdd = $quantityToAdd; // The increment amount
        }

        // Check card limit
        if (!\Gate::forUser($user)->allows('addCards', $actualAmountToAdd)) {
            $limit = $user->cardLimit();
            $currentUsage = $user->currentCardUsage();
            
            return back()->with('error', __('limits.cards.reached.title'))
                ->with('error_detail', __('limits.cards.reached.body_adding', [
                    'amount' => $actualAmountToAdd,
                    'limit' => $limit,
                    'used' => $currentUsage,
                ]));
        }
        
        if ($existingCard) {
            $existingCard->increment('quantity', $quantityToAdd);
            $message = 'Card quantity updated in deck!';
        } else {
            // Get card price from catalog
            $card = TcgcsvProduct::find($validated['product_id']);
            $price = null;
            $currency = 'USD';
            
            if ($card) {
                $latestPrice = $card->prices()->orderBy('updated_at', 'desc')->first();
                if ($latestPrice && $latestPrice->market_price) {
                    $price = $latestPrice->market_price;
                    $currency = 'USD';
                }
            }
            
            DeckCard::create([
                'deck_id' => $deck->id,
                'product_id' => $validated['product_id'],
                'quantity' => $quantityToAdd,
                'cached_price' => $price,
                'cached_price_currency' => $currency,
                'cached_price_updated_at' => $price ? now() : null,
            ]);
            $message = 'Card added to deck!';
        }

        return back()->with('success', $message);
    }

    /**
     * Add a TCGDEX card to the deck
     */
    public function addCardTcgdex(Request $request, Deck $deck): RedirectResponse
    {
        // Authorization check
        if ($deck->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this deck.');
        }

        $validated = $request->validate([
            'tcgdex_card_id' => 'required|integer|exists:tcgdx_cards,id',
            'quantity' => 'nullable|integer|min:1|max:4',
        ]);

        $user = Auth::user();
        $quantityToAdd = $validated['quantity'] ?? 1;
        
        // Check if card already in deck - if so, we only add the difference
        $existingCard = DeckCard::where('deck_id', $deck->id)
            ->where('tcgdex_card_id', $validated['tcgdex_card_id'])
            ->first();

        $actualAmountToAdd = $quantityToAdd;
        if ($existingCard) {
            // We're incrementing, so only count the new quantity
            $actualAmountToAdd = $quantityToAdd; // The increment amount
        }

        // Check card limit
        if (!\Gate::forUser($user)->allows('addCards', $actualAmountToAdd)) {
            $limit = $user->cardLimit();
            $currentUsage = $user->currentCardUsage();
            
            return back()->with('error', __('limits.cards.reached.title'))
                ->with('error_detail', __('limits.cards.reached.body_adding', [
                    'amount' => $actualAmountToAdd,
                    'limit' => $limit,
                    'used' => $currentUsage,
                ]));
        }
        
        if ($existingCard) {
            $existingCard->increment('quantity', $quantityToAdd);
            $message = 'Card quantity updated in deck!';
        } else {
            // Get card price from catalog
            $card = \App\Models\Tcgdx\TcgdxCard::find($validated['tcgdex_card_id']);
            $price = null;
            $currency = 'EUR';
            
            if ($card) {
                if ($card->price_eur && $card->price_eur > 0) {
                    $price = $card->price_eur;
                    $currency = 'EUR';
                } elseif ($card->price_usd && $card->price_usd > 0) {
                    $price = $card->price_usd;
                    $currency = 'USD';
                }
            }
            
            DeckCard::create([
                'deck_id' => $deck->id,
                'tcgdex_card_id' => $validated['tcgdex_card_id'],
                'quantity' => $quantityToAdd,
                'cached_price' => $price,
                'cached_price_currency' => $currency,
                'cached_price_updated_at' => $price ? now() : null,
            ]);
            $message = 'Card added to deck!';
        }

        return back()->with('success', $message);
    }

    /**
     * Add a CMAPI card (Lorcana/One Piece) to deck
     */
    public function addCardCmapi(Request $request, Deck $deck): RedirectResponse
    {
        // Authorization check
        if ($deck->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this deck.');
        }

        $validated = $request->validate([
            'cmapi_card_id' => 'required|string|max:100|exists:cmapi_cards,cmapi_id',
            'quantity' => 'nullable|integer|min:1|max:4',
        ]);

        $user = Auth::user();
        $quantityToAdd = $validated['quantity'] ?? 1;
        
        // Check if card already in deck - if so, we only add the difference
        $existingCard = DeckCard::where('deck_id', $deck->id)
            ->where('cmapi_card_id', $validated['cmapi_card_id'])
            ->first();

        $actualAmountToAdd = $quantityToAdd;
        if ($existingCard) {
            // We're incrementing, so only count the new quantity
            $actualAmountToAdd = $quantityToAdd; // The increment amount
        }

        // Check card limit
        if (!\Gate::forUser($user)->allows('addCards', $actualAmountToAdd)) {
            $limit = $user->cardLimit();
            $currentUsage = $user->currentCardUsage();
            
            return back()->with('error', __('limits.cards.reached.title'))
                ->with('error_detail', __('limits.cards.reached.body_adding', [
                    'amount' => $actualAmountToAdd,
                    'limit' => $limit,
                    'used' => $currentUsage,
                ]));
        }
        
        if ($existingCard) {
            $existingCard->increment('quantity', $quantityToAdd);
            $message = 'Card quantity updated in deck!';
        } else {
            // Get card price from catalog
            $card = \App\Models\Cmapi\CmapiCard::where('cmapi_id', $validated['cmapi_card_id'])->first();
            $price = null;
            $currency = 'EUR';
            
            if ($card && $card->price_eur && $card->price_eur > 0) {
                $price = $card->price_eur;
                $currency = 'EUR';
            }
            
            DeckCard::create([
                'deck_id' => $deck->id,
                'cmapi_card_id' => $validated['cmapi_card_id'],
                'quantity' => $quantityToAdd,
                'cached_price' => $price,
                'cached_price_currency' => $currency,
                'cached_price_updated_at' => $price ? now() : null,
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
     * Share a deck (generate public link)
     */
    public function share(Deck $deck): RedirectResponse
    {
        // Authorize ownership and sharing permission
        if (!\Gate::forUser(Auth::user())->allows('shareDeck', $deck)) {
            $max = Auth::user()->maxSharedDecks();
            $current = Auth::user()->sharedDecksCount();
            
            if ($max === 0) {
                return back()->with('error', __('sharing.limit.free.title'))
                    ->with('error_detail', __('sharing.limit.free.body'));
            }
            
            if ($max !== null && $current >= $max) {
                return back()->with('error', __('sharing.limit.reached.title'))
                    ->with('error_detail', __('sharing.limit.reached.body', [
                        'limit' => $max,
                        'current' => $current,
                    ]));
            }
            
            abort(403, 'Unauthorized action.');
        }

        // Share the deck (generates token)
        $deck->share();

        return back()->with('success', __('sharing.deck.shared'))
            ->with('shared_url', $deck->public_url);
    }

    /**
     * Unshare a deck (revoke public link)
     */
    public function unshare(Deck $deck): RedirectResponse
    {
        // Authorize ownership
        if (!\Gate::forUser(Auth::user())->allows('unshareDeck', $deck)) {
            abort(403, 'Unauthorized action.');
        }

        // Unshare the deck
        $deck->unshare();

        return back()->with('success', __('sharing.deck.unshared'));
    }

    /**
     * Show public deck view (accessible without login)
     */
    public function publicView(string $token): View
    {
        $deck = Deck::where('shared_token', $token)
            ->where('is_shared', true)
            ->with(['deckCards.card.group', 'game', 'user'])
            ->firstOrFail();

        $stats = $this->getDeckStats($deck);
        $topStats = $this->getDeckTopStats($deck);

        return view('decks.public', compact('deck', 'stats', 'topStats'));
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
            ->map(function($dc) {
                $card = null;
                if ($dc->product_id) {
                    $card = $dc->card;
                } elseif ($dc->tcgdex_card_id) {
                    $card = $dc->tcgdexCard;
                } elseif ($dc->cmapi_card_id) {
                    $card = $dc->cmapiCard;
                }
                $dc->_card = $card;
                return $dc;
            })
            ->groupBy(fn($dc) => $dc->_card->rarity ?? 'Unknown')
            ->map(fn($group) => [
                'count' => $group->count(),
                'total_quantity' => $group->sum('quantity')
            ])
            ->sortByDesc('count');

        // 2. Set distribution
        $setDistribution = $deck->deckCards
            ->map(function($dc) {
                $card = null;
                $setName = 'Unknown';
                
                if ($dc->product_id) {
                    $card = $dc->card;
                    $setName = $card->group->name ?? 'Unknown';
                } elseif ($dc->tcgdex_card_id) {
                    $card = $dc->tcgdexCard;
                    $setName = $card->set->name['en'] ?? $card->set->name ?? 'Unknown';
                } elseif ($dc->cmapi_card_id) {
                    $card = $dc->cmapiCard;
                    $setName = $card->set_name ?? 'Unknown';
                }
                
                $dc->_set_name = $setName;
                return $dc;
            })
            ->groupBy(fn($dc) => $dc->_set_name)
            ->map(fn($group) => [
                'set_name' => $group->first()->_set_name,
                'count' => $group->count(),
                'total_quantity' => $group->sum('quantity')
            ])
            ->sortByDesc('count')
            ->take(5);

        // 3. Card values from cached prices
        $totalValueUsd = 0;
        $totalValueEur = 0;
        $cardsWithPricesUsd = 0;
        $cardsWithPricesEur = 0;
        
        foreach ($deck->deckCards as $deckCard) {
            if ($deckCard->cached_price && $deckCard->cached_price > 0) {
                $priceValue = $deckCard->cached_price * $deckCard->quantity;
                
                if ($deckCard->cached_price_currency === 'EUR') {
                    $totalValueEur += $priceValue;
                    $cardsWithPricesEur++;
                } elseif ($deckCard->cached_price_currency === 'USD') {
                    $totalValueUsd += $priceValue;
                    $cardsWithPricesUsd++;
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

    /**
     * Upload a photo for a deck card (Premium only)
     */
    public function uploadPhoto(Request $request, DeckCard $deckCard)
    {
        // Authorization: must own the deck
        if ($deckCard->deck->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Authorization: must be premium
        if (!Gate::allows('uploadCardPhotos')) {
            return back()->with('error', __('photos.upload.not_allowed.title'));
        }

        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120', // 5MB max
        ]);

        $file = $request->file('photo');
        
        // Store in local storage (storage/app/private)
        $path = $file->store('deck-card-photos/' . Auth::id(), 'local');
        
        // Create photo record
        $photo = \App\Models\DeckCardPhoto::create([
            'user_id' => Auth::id(),
            'deck_card_id' => $deckCard->id,
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        return back()->with('success', __('photos.upload.success'));
    }

    /**
     * Serve a photo file (owner only)
     */
    public function servePhoto(\App\Models\DeckCardPhoto $photo)
    {
        // Authorization: must own the photo
        if ($photo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$photo->path || !\Storage::disk('local')->exists($photo->path)) {
            abort(404, 'Photo not found.');
        }

        return response()->file(
            storage_path('app/private/' . $photo->path),
            ['Content-Type' => $photo->mime_type ?? 'image/jpeg']
        );
    }

    /**
     * Delete a photo (owner only)
     */
    public function deletePhoto(\App\Models\DeckCardPhoto $photo)
    {
        // Authorization: must own the photo
        if ($photo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $photo->delete(); // Will also delete file via model event

        return back()->with('success', __('photos.delete.success'));
    }
}
