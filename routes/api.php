<?php

use App\Http\Controllers\Api\CardSearchController;
use Illuminate\Support\Facades\Route;

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
