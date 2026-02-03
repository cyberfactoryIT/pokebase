<?php

use App\Http\Controllers\Api\CardSearchController;
use App\Http\Controllers\Api\ExpansionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/**
 * Public API routes (no authentication required for read operations)
 */

/**
 * Global card search endpoint
 * GET /api/search/cards?q=charizard&limit=12
 * Returns typeahead-ready card suggestions across all sets/expansions
 * 
 * Uses web middleware to support session-based authentication for collection filtering
 */
Route::middleware(['web'])->get('/search/cards', [CardSearchController::class, 'index'])->name('api.search.cards');

/**
 * Get missing cards for expansion (requires authentication)
 * GET /api/expansions/{id}/missing-cards
 * Returns cards not in user's collection for a specific expansion
 */
Route::middleware(['web', 'auth'])->get('/expansions/{id}/missing-cards', [ExpansionController::class, 'getMissingCards'])
    ->name('api.expansions.missing-cards');

/**
 * Get price history for CMAPI card
 * GET /api/cmapi/cards/{id}/price-history?language=en&condition=NM&days=30
 * Returns historical price data for charting (CardMarket S3 data for Lorcana)
 */
Route::get('/cmapi/cards/{id}/price-history', function ($id) {
    $days = request('days', 30);
    $cutoffDate = now()->subDays($days);
    
    // Get the card to determine game and cardmarket_id
    $card = \App\Models\Cmapi\CmapiCard::find($id);
    
    if (!$card) {
        return response()->json([], 404);
    }
    
    // For Lorcana: use CardMarket S3 price data (cardmarket_price_quotes_lorcana)
    if ($card->game === 'lorcana' && $card->cardmarket_id) {
        $priceHistory = DB::table('cardmarket_price_quotes_lorcana')
            ->where('cardmarket_product_id', $card->cardmarket_id)
            ->where('as_of_date', '>=', $cutoffDate)
            ->orderBy('as_of_date', 'asc')
            ->get()
            ->map(function ($quote) {
                return [
                    'price_date' => $quote->as_of_date,
                    'price_eur' => $quote->trend ?? $quote->avg ?? $quote->low,
                    'price_trend_eur' => $quote->trend ?? $quote->avg30 ?? $quote->avg,
                ];
            });
        
        if ($priceHistory->isNotEmpty()) {
            $collection = $priceHistory;
            
            // Get latest quote for avg7 and avg30 reference points
            $latestQuote = DB::table('cardmarket_price_quotes_lorcana')
                ->where('cardmarket_product_id', $card->cardmarket_id)
                ->orderBy('as_of_date', 'desc')
                ->first();
            
            if ($latestQuote) {
                $today = now();
                $oldestDate = \Carbon\Carbon::parse($collection->first()['price_date']);
                $daysOfHistory = $today->diffInDays($oldestDate);
                
                // If less than 7 days of history and avg7 exists, add synthetic point at -7 days
                if ($daysOfHistory < 7 && $latestQuote->avg7) {
                    $collection->prepend([
                        'price_date' => $today->copy()->subDays(7)->format('Y-m-d'),
                        'price_eur' => $latestQuote->avg7,
                        'price_trend_eur' => $latestQuote->avg7,
                    ]);
                }
                
                // If less than 30 days of history and avg30 exists, add synthetic point at -30 days
                if ($daysOfHistory < 30 && $latestQuote->avg30) {
                    $collection->prepend([
                        'price_date' => $today->copy()->subDays(30)->format('Y-m-d'),
                        'price_eur' => $latestQuote->avg30,
                        'price_trend_eur' => $latestQuote->avg30,
                    ]);
                }
                
                // Sort by date again
                $collection = $collection->sortBy('price_date')->values();
            }
            
            return response()->json($collection);
        }
    }
    
    // For One Piece or fallback: Try old CMAPI price history
    $language = request('language', 'en');
    $condition = request('condition', 'NM');
    
    $cardmarketHistory = DB::table('cmapi_price_history')
        ->where('cmapi_card_id', $id)
        ->where('language', $language)
        ->where('condition', $condition)
        ->where('price_date', '>=', $cutoffDate)
        ->orderBy('price_date', 'asc')
        ->get(['price_date', 'price_eur', 'price_trend_eur']);
    
    if ($cardmarketHistory->isNotEmpty()) {
        return response()->json($cardmarketHistory);
    }
    
    // Final fallback: RapidAPI snapshots
    $rapidapiHistory = DB::table('cmapi_card_price_snapshots')
        ->where('cmapi_card_id', $id)
        ->where('condition', $condition)
        ->where(function($query) use ($language) {
            $query->where('language', $language)
                  ->orWhereNull('language');
        })
        ->where('recorded_at', '>=', $cutoffDate)
        ->orderBy('recorded_at', 'asc')
        ->get()
        ->map(function ($snapshot) {
            return [
                'price_date' => $snapshot->recorded_at,
                'price_eur' => $snapshot->price_eur,
                'price_trend_eur' => $snapshot->price_eur,
            ];
        });
    
    return response()->json($rapidapiHistory);
})->name('api.cmapi.cards.price-history');

/**
 * Get missing cards for TCGDEX set (requires authentication)
 * GET /api/pokemon/sets/{tcgdexId}/missing
 * Returns cards not in user's collection for a specific TCGDEX set
 */
Route::middleware(['web', 'auth'])->get('/pokemon/sets/{tcgdexId}/missing', [ExpansionController::class, 'getMissingCardsTcgdex'])
    ->name('api.pokemon.sets.missing');

/**
 * Get user's popular sets (most complete sets)
 * GET /api/user/popular-sets
 * Returns user's sets ordered by completion percentage
 */
Route::middleware(['web', 'auth'])->get('/user/popular-sets', [ExpansionController::class, 'getPopularSets'])
    ->name('api.user.popular-sets');

/**
 * Add filtered collection cards to deck
 * POST /api/collection/add-filtered-to-deck
 * Adds all cards matching current filters to specified deck
 */
Route::middleware(['web', 'auth'])->post('/collection/add-filtered-to-deck', [\App\Http\Controllers\CollectionController::class, 'addFilteredToDeck'])
    ->name('api.collection.add-filtered-to-deck');

/**
 * Create deck and add filtered collection cards
 * POST /api/collection/create-deck-with-filtered
 * Creates new deck and adds all cards matching current filters
 */
Route::middleware(['web', 'auth'])->post('/collection/create-deck-with-filtered', [\App\Http\Controllers\CollectionController::class, 'createDeckWithFiltered'])
    ->name('api.collection.create-deck-with-filtered');

/**
 * Add selected collection cards to deck
 * POST /api/collection/add-selected-to-deck
 * Adds user-selected cards to specified deck
 */
Route::middleware(['web', 'auth'])->post('/collection/add-selected-to-deck', [\App\Http\Controllers\CollectionController::class, 'addSelectedToDeck'])
    ->name('api.collection.add-selected-to-deck');

/**
 * Create deck and add selected collection cards
 * POST /api/collection/create-deck-with-selected
 * Creates new deck and adds user-selected cards
 */
Route::middleware(['web', 'auth'])->post('/collection/create-deck-with-selected', [\App\Http\Controllers\CollectionController::class, 'createDeckWithSelected'])
    ->name('api.collection.create-deck-with-selected');

/**
 * Stripe Webhook endpoint
 * POST /api/stripe/webhook
 * Handles subscription events from Stripe (renewals, cancellations, failures)
 * No CSRF protection needed (Stripe signature verification used instead)
 */
Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handleWebhook'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
