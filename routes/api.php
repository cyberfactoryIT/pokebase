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
 * Returns historical price data for charting (CardMarket or RapidAPI fallback)
 */
Route::get('/cmapi/cards/{id}/price-history', function ($id) {
    $language = request('language', 'en');
    $condition = request('condition', 'NM');
    $days = request('days', 30);
    $cutoffDate = now()->subDays($days);
    
    // Try CardMarket history first (more detailed with conditions)
    $cardmarketHistory = DB::table('cmapi_price_history')
        ->where('cmapi_card_id', $id)
        ->where('language', $language)
        ->where('condition', $condition)
        ->where('price_date', '>=', $cutoffDate)
        ->orderBy('price_date', 'asc')
        ->get(['price_date', 'price_eur', 'price_trend_eur']);
    
    // If CardMarket has data, use it
    if ($cardmarketHistory->isNotEmpty()) {
        return response()->json($cardmarketHistory);
    }
    
    // Fallback to RapidAPI snapshots (no condition filtering, simpler)
    $rapidapiHistory = DB::table('cmapi_card_price_snapshots')
        ->where('cmapi_card_id', $id)
        ->where('condition', $condition)
        ->where(function($query) use ($language) {
            $query->where('language', $language)
                  ->orWhereNull('language'); // Include language-neutral prices
        })
        ->where('recorded_at', '>=', $cutoffDate)
        ->orderBy('recorded_at', 'asc')
        ->get()
        ->map(function ($snapshot) {
            return [
                'price_date' => $snapshot->recorded_at,
                'price_eur' => $snapshot->price_eur,
                'price_trend_eur' => $snapshot->price_eur, // No trend calculation for snapshots
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
 * Stripe Webhook endpoint
 * POST /api/stripe/webhook
 * Handles subscription events from Stripe (renewals, cancellations, failures)
 * No CSRF protection needed (Stripe signature verification used instead)
 */
Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handleWebhook'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
