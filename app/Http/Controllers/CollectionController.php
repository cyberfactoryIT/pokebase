<?php

namespace App\Http\Controllers;

use App\Models\UserCollection;
use App\Models\UserCardPhoto;
use App\Models\TcgcsvProduct;
use App\Services\CollectionInsightsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CollectionController extends Controller
{
    /**
     * Export user's collection as CSV (Advanced/Premium only)
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! ($user->isAdvanced() || $user->isPremium())) {
            abort(403, 'Export is available for Advanced and Premium plans only.');
        }

        $userId = $user->id;
        $currentGame = $request->attributes->get('currentGame');
        $catalogBackend = catalog_backend();

        $query = UserCollection::where('user_id', $userId);

        if ($catalogBackend === 'tcgdex') {
            $query->whereNotNull('tcgdex_card_id')
                ->when($currentGame, function ($q) use ($currentGame) {
                    $q->whereHas('tcgdexCard.set', fn ($sq) => $sq->where('game_id', $currentGame->id));
                })
                ->with(['tcgdexCard.set']);
        } elseif ($catalogBackend === 'cmapi') {
            $query->whereNotNull('cmapi_card_id')
                ->when($currentGame, function ($q) use ($currentGame) {
                    $q->whereHas('cmapiCard', fn ($sq) => $sq->where('game', $currentGame->slug));
                })
                ->with(['cmapiCard.set']);
        } else {
            // TCGCSV
            $query->whereNotNull('product_id')
                ->when($currentGame, function ($q) use ($currentGame) {
                    $q->whereHas('card', fn ($sq) => $sq->where('game_id', $currentGame->id));
                })
                ->with(['card.group']);
        }

        $filename = 'collection-' . $catalogBackend . '-' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function () use ($query, $catalogBackend) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Backend', 'Game', 'Set', 'Card', 'Number', 'Quantity', 'Rarity', 'Price_EUR']);

            $query->chunk(500, function ($items) use ($out, $catalogBackend) {
                foreach ($items as $item) {
                    if ($catalogBackend === 'tcgdex') {
                        $card = $item->tcgdexCard;
                        if (! $card) {
                            continue;
                        }
                        $set = $card->set;

                        // Normalize JSON names for TCGDEX (set and card)
                        $setName = optional($set)->name;
                        // Handle JSON string or array for set name
                        if (is_string($setName) && str_starts_with($setName, '{')) {
                            $decoded = json_decode($setName, true);
                            $setName = is_array($decoded) ? ($decoded['en'] ?? reset($decoded) ?? 'Unknown') : $setName;
                        } elseif (is_array($setName)) {
                            $setName = $setName['en'] ?? reset($setName) ?? 'Unknown';
                        }

                        $cardName = $card->name;
                        // Handle JSON string or array for card name
                        if (is_string($cardName) && str_starts_with($cardName, '{')) {
                            $decodedCard = json_decode($cardName, true);
                            $cardName = is_array($decodedCard) ? ($decodedCard['en'] ?? reset($decodedCard) ?? 'Unknown') : $cardName;
                        } elseif (is_array($cardName)) {
                            $cardName = $cardName['en'] ?? reset($cardName) ?? 'Unknown';
                        }

                        fputcsv($out, [
                            'tcgdex',
                            optional($set)->game_id,
                            $setName,
                            $cardName,
                            $card->local_id,
                            $item->quantity,
                            $card->rarity,
                            $item->cached_price,
                        ]);
                    } elseif ($catalogBackend === 'cmapi') {
                        $card = $item->cmapiCard;
                        if (! $card) {
                            continue;
                        }
                        $set = $card->set;
                        $setName = optional($set)->name;
                        if (is_array($setName)) {
                            $setName = $setName['en'] ?? reset($setName) ?? 'Unknown';
                        }
                        $cardName = $card->name;
                        if (is_array($cardName)) {
                            $cardName = $cardName['en'] ?? reset($cardName) ?? 'Unknown';
                        }
                        fputcsv($out, [
                            'cmapi',
                            $card->game,
                            $setName,
                            $cardName,
                            $card->number,
                            $item->quantity,
                            $card->rarity,
                            $item->cached_price,
                        ]);
                    } else {
                        $card = $item->card;
                        if (! $card) {
                            continue;
                        }
                        $group = $card->group;
                        $groupName = optional($group)->name;
                        if (is_array($groupName)) {
                            $groupName = $groupName['en'] ?? reset($groupName) ?? 'Unknown';
                        }
                        $cardName = $card->name;
                        if (is_array($cardName)) {
                            $cardName = $cardName['en'] ?? reset($cardName) ?? 'Unknown';
                        }
                        fputcsv($out, [
                            'tcgcsv',
                            $card->game_id,
                            $groupName,
                            $cardName,
                            $card->card_number,
                            $item->quantity,
                            $card->rarity,
                            $item->cached_price,
                        ]);
                    }
                }
            });

            fclose($out);
        }, 200, $headers);
    }
    /**
     * Display user's collection
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $currentGame = $request->attributes->get('currentGame');
        $catalogBackend = catalog_backend();
        
        // Get filter parameters
        $rarityFilter = $request->input('rarity');
        $setFilter = $request->input('set');
        $letterFilter = $request->input('letter'); // A, B, C, etc.
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $sortOrder = $request->input('sort', 'newest'); // newest, a-z, z-a, price-asc, price-desc
        
        $query = UserCollection::where('user_id', $userId);
        
        // Load relations based on catalog backend
        if ($catalogBackend === 'tcgdex') {
            $query->with(['tcgdexCard', 'photos'])
                  ->whereNotNull('tcgdex_card_id');
            
            // Apply letter filter for TCGDEX
            if ($letterFilter) {
                $query->whereHas('tcgdexCard', function($q) use ($letterFilter) {
                    $q->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.en")) LIKE ?', [$letterFilter . '%']);
                });
            }
            
            // Apply rarity filter for TCGDEX
            if ($rarityFilter) {
                $query->whereHas('tcgdexCard', function($q) use ($rarityFilter) {
                    $q->where('rarity', $rarityFilter);
                });
            }
            
            // Apply set filter for TCGDEX
            if ($setFilter) {
                $query->whereHas('tcgdexCard.set', function($q) use ($setFilter) {
                    $q->where('tcgdx_sets.id', $setFilter);
                });
            }
        } elseif ($catalogBackend === 'cmapi') {
            $query->with(['cmapiCard', 'photos'])
                  ->whereNotNull('cmapi_card_id');
            
            // Apply letter filter for CMAPI
            if ($letterFilter) {
                $query->whereHas('cmapiCard', function($q) use ($letterFilter) {
                    $q->where('name', 'LIKE', $letterFilter . '%');
                });
            }
            
            // Apply rarity filter for CMAPI
            if ($rarityFilter) {
                $query->whereHas('cmapiCard', function($q) use ($rarityFilter) {
                    $q->where('rarity', $rarityFilter);
                });
            }
            
            // Apply set filter for CMAPI
            if ($setFilter) {
                $query->whereHas('cmapiCard', function($q) use ($setFilter) {
                    $q->where('set_name', $setFilter);
                });
            }
        } else {
            $query->with(['card.group', 'card.rapidapiCard', 'card.prices', 'card.cardmarketProduct.latestPriceQuote', 'photos'])
                  ->whereNotNull('product_id');
                  
            // Filter by current game (only for TCGCSV)
            if ($currentGame) {
                $query->whereHas('card', function($q) use ($currentGame) {
                    $q->where('game_id', $currentGame->id);
                });
            }
            
            // Apply letter filter for TCGCSV
            if ($letterFilter) {
                $query->whereHas('card', function($q) use ($letterFilter) {
                    $q->where('name', 'LIKE', $letterFilter . '%');
                });
            }
            
            // Apply rarity filter for TCGCSV
            if ($rarityFilter) {
                $query->whereHas('card', function($q) use ($rarityFilter) {
                    $q->where('rarity', $rarityFilter);
                });
            }
            
            // Apply set filter for TCGCSV
            if ($setFilter) {
                $query->whereHas('card.group', function($q) use ($setFilter) {
                    $q->where('name', $setFilter);
                });
            }
        }
        
        // Get price range for comparison (before filtering)
        $actualPriceRange = ['min' => 0, 'max' => 100];
        if (Gate::allows('seePrices')) {
            $user = Auth::user();
            $preferredCurrency = $user->preferred_currency ?? 'EUR';
            
            $priceCheckQuery = UserCollection::where('user_id', $userId)
                ->whereNotNull('cached_price')
                ->where('cached_price', '>', 0)
                ->select('cached_price', 'cached_price_currency');
            
            if ($catalogBackend === 'tcgdex') {
                $priceCheckQuery->whereNotNull('tcgdex_card_id');
            } elseif ($catalogBackend === 'cmapi') {
                $priceCheckQuery->whereNotNull('cmapi_card_id');
            } else {
                $priceCheckQuery->whereNotNull('product_id');
                if ($currentGame) {
                    $priceCheckQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
                }
            }
            
            $pricesForRange = $priceCheckQuery->get();
            
            if ($pricesForRange->isNotEmpty()) {
                $convertedPrices = $pricesForRange->map(function($item) use ($preferredCurrency) {
                    $currency = $item->cached_price_currency ?? 'EUR';
                    return \App\Services\CurrencyService::convert($item->cached_price, $currency, $preferredCurrency);
                });
                
                $actualPriceRange = [
                    'min' => $convertedPrices->min(),
                    'max' => $convertedPrices->max(),
                ];
            }
        }
        
        // Apply price range filter (Premium only) - but only if user has customized the range
        $isPriceFilterActive = ($minPrice !== null && $minPrice > $actualPriceRange['min']) 
                             || ($maxPrice !== null && $maxPrice < $actualPriceRange['max']);
        
        if ($isPriceFilterActive && Gate::allows('seePrices')) {
            // Convert user's input from preferred currency to EUR for comparison
            $user = Auth::user();
            $preferredCurrency = $user->preferred_currency ?? 'EUR';
            
            // We need to filter in PHP since cached_price_currency varies per row
            // Get all collection IDs that match the price range (filtered by backend)
            $priceFilterQuery = UserCollection::where('user_id', $userId)
                ->whereNotNull('cached_price')
                ->where('cached_price', '>', 0)
                ->select('id', 'cached_price', 'cached_price_currency');
            
            // Apply backend filter to price query
            if ($catalogBackend === 'tcgdex') {
                $priceFilterQuery->whereNotNull('tcgdex_card_id');
            } elseif ($catalogBackend === 'cmapi') {
                $priceFilterQuery->whereNotNull('cmapi_card_id');
            } else {
                $priceFilterQuery->whereNotNull('product_id');
                if ($currentGame) {
                    $priceFilterQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
                }
            }
            
            $priceItems = $priceFilterQuery->get();
            
            $validIds = $priceItems->filter(function($item) use ($minPrice, $maxPrice, $preferredCurrency) {
                $currency = $item->cached_price_currency ?? 'EUR';
                // Convert cached price to user's preferred currency
                $priceInPreferred = \App\Services\CurrencyService::convert($item->cached_price, $currency, $preferredCurrency);
                
                $matchesMin = $minPrice === null || $priceInPreferred >= $minPrice;
                $matchesMax = $maxPrice === null || $priceInPreferred <= $maxPrice;
                
                return $matchesMin && $matchesMax;
            })->pluck('id')->toArray();
            
            // Also get IDs of cards without price (they should always be included)
            $noPriceQuery = UserCollection::where('user_id', $userId)
                ->where(function($q) {
                    $q->whereNull('cached_price')
                      ->orWhere('cached_price', '<=', 0);
                })
                ->select('id');
            
            // Apply backend filter to no-price query
            if ($catalogBackend === 'tcgdex') {
                $noPriceQuery->whereNotNull('tcgdex_card_id');
            } elseif ($catalogBackend === 'cmapi') {
                $noPriceQuery->whereNotNull('cmapi_card_id');
            } else {
                $noPriceQuery->whereNotNull('product_id');
                if ($currentGame) {
                    $noPriceQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
                }
            }
            
            $noPriceIds = $noPriceQuery->pluck('id')->toArray();
            
            // Merge cards with valid prices and cards without prices
            $allValidIds = array_merge($validIds, $noPriceIds);
            
            if (!empty($allValidIds)) {
                $query->whereIn('user_collection.id', $allValidIds);
            } else {
                // No items match the price filter, return empty result
                $query->whereRaw('1 = 0');
            }
        }
        
        // Apply sorting
        if ($sortOrder === 'a-z') {
            // Sort alphabetically A-Z by card name
            if ($catalogBackend === 'tcgdex') {
                $query->join('tcgdx_cards', 'user_collection.tcgdex_card_id', '=', 'tcgdx_cards.id')
                      ->orderByRaw('JSON_UNQUOTE(JSON_EXTRACT(tcgdx_cards.name, "$.en")) ASC')
                      ->select('user_collection.*');
            } elseif ($catalogBackend === 'cmapi') {
                $query->join('cmapi_cards', 'user_collection.cmapi_card_id', '=', 'cmapi_cards.cmapi_id')
                      ->orderBy('cmapi_cards.name', 'ASC')
                      ->select('user_collection.*');
            } else {
                $query->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.id')
                      ->orderBy('tcgcsv_products.name', 'ASC')
                      ->select('user_collection.*');
            }
        } elseif ($sortOrder === 'z-a') {
            // Sort alphabetically Z-A by card name
            if ($catalogBackend === 'tcgdex') {
                $query->join('tcgdx_cards', 'user_collection.tcgdex_card_id', '=', 'tcgdx_cards.id')
                      ->orderByRaw('JSON_UNQUOTE(JSON_EXTRACT(tcgdx_cards.name, "$.en")) DESC')
                      ->select('user_collection.*');
            } elseif ($catalogBackend === 'cmapi') {
                $query->join('cmapi_cards', 'user_collection.cmapi_card_id', '=', 'cmapi_cards.cmapi_id')
                      ->orderBy('cmapi_cards.name', 'DESC')
                      ->select('user_collection.*');
            } else {
                $query->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.id')
                      ->orderBy('tcgcsv_products.name', 'DESC')
                      ->select('user_collection.*');
            }
        } elseif ($sortOrder === 'price-asc') {
            // Sort by price low to high
            $query->orderBy('cached_price', 'ASC');
        } elseif ($sortOrder === 'price-desc') {
            // Sort by price high to low
            $query->orderBy('cached_price', 'DESC');
        } else {
            // Default: newest first
            $query->orderBy('created_at', 'desc');
        }
        
        $collection = $query->paginate(24)->appends($request->except('page'));

        // Get available letters, sets and rarities for filters
        $availableLetters = $this->getAvailableLetters($userId, $currentGame, $catalogBackend);
        $availableSets = $this->getAvailableSets($userId, $currentGame, $catalogBackend);
        $availableRarities = $this->getAvailableRarities($userId, $currentGame, $catalogBackend);
        
        // Use the price range calculated earlier (for consistency)
        $priceRange = $actualPriceRange;

        // Basic stats (filtered by game)
        $stats = [
            'total_cards' => $this->getUserCardCount($userId, $currentGame, $catalogBackend),
            'unique_cards' => $this->getUserUniqueCardCount($userId, $currentGame, $catalogBackend),
            'foil_cards' => $this->getUserFoilCardCount($userId, $currentGame, $catalogBackend),
        ];

        // Top 3 interesting stats for header
        $topStats = $this->getTopStats($userId, $currentGame, $catalogBackend);
        
        // Detailed statistics for stats tab
        $detailedStats = $this->getDetailedStats($userId, $currentGame, $catalogBackend);
        
        // Generate insights for statistics tab
        $insightsService = new CollectionInsightsService();
        $rarityInsight = $insightsService->generateRarityInsight($topStats['rarity_distribution']);
        $conditionInsight = $insightsService->generateConditionInsight($detailedStats['condition_distribution']);
        $focusSet = $insightsService->identifyFocusSet($detailedStats['top_sets']);
        $setsInsight = $insightsService->generateSetsInsight($detailedStats['top_sets'], $focusSet ?? []);
        
        // Calculate collection value (with rarity filter applied)
        $valuation = $this->calculateCollectionValue($userId, $currentGame, $catalogBackend, $rarityFilter);

        return view('collection.index', compact('collection', 'stats', 'topStats', 'detailedStats', 'valuation', 'rarityInsight', 'conditionInsight', 'setsInsight', 'focusSet', 'availableLetters', 'availableSets', 'availableRarities', 'priceRange', 'catalogBackend'));
    }
    
    /**
     * Get available first letters in user's collection
     */
    private function getAvailableLetters($userId, $currentGame, $catalogBackend): array
    {
        if ($catalogBackend === 'tcgdex') {
            $letters = UserCollection::where('user_id', $userId)
                ->whereNotNull('tcgdex_card_id')
                ->join('tcgdx_cards', 'user_collection.tcgdex_card_id', '=', 'tcgdx_cards.id')
                ->selectRaw('UPPER(LEFT(JSON_UNQUOTE(JSON_EXTRACT(tcgdx_cards.name, "$.en")), 1)) as letter, COUNT(*) as count')
                ->groupBy('letter')
                ->orderBy('letter', 'asc')
                ->get()
                ->pluck('letter')
                ->toArray();
        } elseif ($catalogBackend === 'cmapi') {
            $letters = UserCollection::where('user_id', $userId)
                ->whereNotNull('cmapi_card_id')
                ->join('cmapi_cards', 'user_collection.cmapi_card_id', '=', 'cmapi_cards.cmapi_id')
                ->selectRaw('UPPER(LEFT(cmapi_cards.name, 1)) as letter, COUNT(*) as count')
                ->groupBy('letter')
                ->orderBy('letter', 'asc')
                ->get()
                ->pluck('letter')
                ->toArray();
        } else {
            $query = UserCollection::where('user_id', $userId)
                ->whereNotNull('product_id')
                ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.id')
                ->selectRaw('UPPER(LEFT(tcgcsv_products.name, 1)) as letter, COUNT(*) as count')
                ->groupBy('letter')
                ->orderBy('letter', 'asc');
            
            if ($currentGame) {
                $query->where('tcgcsv_products.game_id', $currentGame->id);
            }
            
            $letters = $query->get()->pluck('letter')->toArray();
        }
        
        // Filter to only A-Z letters (remove numbers and special chars)
        return array_values(array_filter($letters, function($letter) {
            return preg_match('/^[A-Z]$/', $letter);
        }));
    }
    
    /**
     * Get available sets in user's collection
     */
    private function getAvailableSets($userId, $currentGame, $catalogBackend): array
    {
        if ($catalogBackend === 'tcgdex') {
            return UserCollection::where('user_id', $userId)
                ->whereNotNull('tcgdex_card_id')
                ->join('tcgdx_cards', 'user_collection.tcgdex_card_id', '=', 'tcgdx_cards.id')
                ->join('tcgdx_sets', 'tcgdx_cards.set_tcgdx_id', '=', 'tcgdx_sets.id')
                ->selectRaw('tcgdx_sets.id, JSON_UNQUOTE(JSON_EXTRACT(tcgdx_sets.name, "$.en")) as name, COUNT(*) as card_count')
                ->groupBy('tcgdx_sets.id', 'name')
                ->orderBy('name', 'asc')
                ->get()
                ->toArray();
        } elseif ($catalogBackend === 'cmapi') {
            return UserCollection::where('user_id', $userId)
                ->whereNotNull('cmapi_card_id')
                ->join('cmapi_cards', 'user_collection.cmapi_card_id', '=', 'cmapi_cards.cmapi_id')
                ->join('cmapi_sets', 'cmapi_cards.set_cmapi_id', '=', 'cmapi_sets.id')
                ->selectRaw('cmapi_sets.name, COUNT(*) as card_count')
                ->whereNotNull('cmapi_sets.name')
                ->groupBy('cmapi_sets.name')
                ->orderBy('cmapi_sets.name', 'asc')
                ->get()
                ->toArray();
        } else {
            $query = UserCollection::where('user_id', $userId)
                ->whereNotNull('product_id')
                ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.id')
                ->join('tcgcsv_groups', 'tcgcsv_products.group_id', '=', 'tcgcsv_groups.id')
                ->selectRaw('tcgcsv_groups.name, COUNT(*) as card_count')
                ->groupBy('tcgcsv_groups.name')
                ->orderBy('tcgcsv_groups.name', 'asc');
            
            if ($currentGame) {
                $query->where('tcgcsv_products.game_id', $currentGame->id);
            }
            
            return $query->get()->toArray();
        }
    }
    
    /**
     * Get available rarities in user's collection
     */
    private function getAvailableRarities($userId, $currentGame, $catalogBackend): array
    {
        if ($catalogBackend === 'tcgdex') {
            return UserCollection::where('user_id', $userId)
                ->whereNotNull('tcgdex_card_id')
                ->join('tcgdx_cards', 'user_collection.tcgdex_card_id', '=', 'tcgdx_cards.id')
                ->selectRaw('tcgdx_cards.rarity, COUNT(*) as card_count')
                ->whereNotNull('tcgdx_cards.rarity')
                ->groupBy('tcgdx_cards.rarity')
                ->orderBy('tcgdx_cards.rarity', 'asc')
                ->get()
                ->toArray();
        } elseif ($catalogBackend === 'cmapi') {
            return UserCollection::where('user_id', $userId)
                ->whereNotNull('cmapi_card_id')
                ->join('cmapi_cards', 'user_collection.cmapi_card_id', '=', 'cmapi_cards.cmapi_id')
                ->selectRaw('cmapi_cards.rarity, COUNT(*) as card_count')
                ->whereNotNull('cmapi_cards.rarity')
                ->groupBy('cmapi_cards.rarity')
                ->orderBy('cmapi_cards.rarity', 'asc')
                ->get()
                ->toArray();
        } else {
            $query = UserCollection::where('user_id', $userId)
                ->whereNotNull('product_id')
                ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.id')
                ->selectRaw('tcgcsv_products.rarity, COUNT(*) as card_count')
                ->whereNotNull('tcgcsv_products.rarity')
                ->groupBy('tcgcsv_products.rarity')
                ->orderBy('tcgcsv_products.rarity', 'asc');
            
            if ($currentGame) {
                $query->where('tcgcsv_products.game_id', $currentGame->id);
            }
            
            return $query->get()->toArray();
        }
    }
    
    private function getUserCardCount($userId, $currentGame, $catalogBackend)
    {
        $query = UserCollection::where('user_id', $userId);
        if ($currentGame) {
            $query->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
        }
        // Filter by catalog backend
        if ($catalogBackend === 'tcgdex') {
            $query->whereNotNull('tcgdex_card_id');
        } else {
            $query->whereNotNull('product_id');
        }
        return $query->sum('quantity');
    }
    
    private function getUserUniqueCardCount($userId, $currentGame, $catalogBackend)
    {
        $query = UserCollection::where('user_id', $userId);
        if ($currentGame) {
            $query->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
        }
        // Filter by catalog backend
        if ($catalogBackend === 'tcgdex') {
            $query->whereNotNull('tcgdex_card_id');
        } else {
            $query->whereNotNull('product_id');
        }
        return $query->count();
    }
    
    private function getUserFoilCardCount($userId, $currentGame, $catalogBackend)
    {
        $query = UserCollection::where('user_id', $userId)->where('is_foil', true);
        if ($currentGame) {
            $query->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
        }
        // Filter by catalog backend
        if ($catalogBackend === 'tcgdex') {
            $query->whereNotNull('tcgdex_card_id');
        } else {
            $query->whereNotNull('product_id');
        }
        return $query->sum('quantity');
    }
    
    /**
     * Get top 3 interesting stats for header
     */
    private function getTopStats($userId, $currentGame, $catalogBackend): array
    {
        // 1. Rarity distribution (most interesting)
        if ($catalogBackend === 'tcgdex') {
            // TCGDEX: rarity is stored in JSON
            $rarityQuery = UserCollection::where('user_id', $userId)
                ->join('tcgdx_cards', 'user_collection.tcgdex_card_id', '=', 'tcgdx_cards.id')
                ->selectRaw('tcgdx_cards.rarity, COUNT(*) as count, SUM(user_collection.quantity) as total_quantity')
                ->whereNotNull('user_collection.tcgdex_card_id')
                ->groupBy('tcgdx_cards.rarity')
                ->orderBy('count', 'desc');
            $rarityDistribution = $rarityQuery->get();
        } else {
            // TCGCSV
            $rarityQuery = UserCollection::where('user_id', $userId)
                ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.product_id')
                ->selectRaw('tcgcsv_products.rarity, COUNT(*) as count, SUM(user_collection.quantity) as total_quantity')
                ->whereNotNull('user_collection.product_id')
                ->groupBy('tcgcsv_products.rarity')
                ->orderBy('count', 'desc');
                
            if ($currentGame) {
                $rarityQuery->where('tcgcsv_products.game_id', $currentGame->id);
            }
            $rarityDistribution = $rarityQuery->get();
        }
        
        // 2. Foil percentage
        $totalCards = $this->getUserCardCount($userId, $currentGame, $catalogBackend);
        $foilCards = $this->getUserFoilCardCount($userId, $currentGame, $catalogBackend);
        $foilPercentage = $totalCards > 0 ? round(($foilCards / $totalCards) * 100, 1) : 0;
        
        // 3. Set completion (top set)
        if ($catalogBackend === 'tcgdex') {
            // TCGDEX
            $topSetQuery = UserCollection::where('user_id', $userId)
                ->join('tcgdx_cards', 'user_collection.tcgdex_card_id', '=', 'tcgdx_cards.id')
                ->join('tcgdx_sets', 'tcgdx_cards.set_tcgdx_id', '=', 'tcgdx_sets.id')
                ->selectRaw('tcgdx_sets.id, tcgdx_sets.name, COUNT(DISTINCT user_collection.tcgdex_card_id) as owned_count')
                ->whereNotNull('user_collection.tcgdex_card_id')
                ->groupBy('tcgdx_sets.id', 'tcgdx_sets.name')
                ->orderBy('owned_count', 'desc')
                ->first();
            
            $setCompletion = null;
            if ($topSetQuery) {
                $setName = $topSetQuery->name;
                if (is_string($setName) && str_starts_with($setName, '{')) {
                    $setName = json_decode($setName, true);
                }
                $setNameEn = is_array($setName) ? ($setName['en'] ?? $setName['fr'] ?? 'Unknown') : $setName;
                
                $totalInSet = \App\Models\Tcgdx\TcgdxCard::where('set_tcgdx_id', $topSetQuery->id)->count();
                $completionPercentage = $totalInSet > 0 ? round(($topSetQuery->owned_count / $totalInSet) * 100, 1) : 0;
                $setCompletion = [
                    'name' => $setNameEn,
                    'owned' => $topSetQuery->owned_count,
                    'total' => $totalInSet,
                    'percentage' => $completionPercentage
                ];
            }
        } else {
            // TCGCSV
            $topSetQuery = UserCollection::where('user_id', $userId)
                ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.product_id')
                ->join('tcgcsv_groups', 'tcgcsv_products.group_id', '=', 'tcgcsv_groups.group_id')
                ->selectRaw('tcgcsv_groups.group_id, tcgcsv_groups.name, COUNT(DISTINCT user_collection.product_id) as owned_count')
                ->whereNotNull('user_collection.product_id')
                ->groupBy('tcgcsv_groups.group_id', 'tcgcsv_groups.name')
                ->orderBy('owned_count', 'desc');
                
            if ($currentGame) {
                $topSetQuery->where('tcgcsv_groups.game_id', $currentGame->id);
            }
            $topSet = $topSetQuery->first();
            
            $setCompletion = null;
            if ($topSet) {
                $totalInSetQuery = TcgcsvProduct::where('group_id', $topSet->group_id);
                if ($currentGame) {
                    $totalInSetQuery->where('game_id', $currentGame->id);
                }
                $totalInSet = $totalInSetQuery->count();
                $completionPercentage = $totalInSet > 0 ? round(($topSet->owned_count / $totalInSet) * 100, 1) : 0;
                $setCompletion = [
                    'name' => $topSet->name,
                    'owned' => $topSet->owned_count,
                    'total' => $totalInSet,
                    'percentage' => $completionPercentage
                ];
            }
        }
        
        return [
            'rarity_distribution' => $rarityDistribution,
            'foil_percentage' => $foilPercentage,
            'foil_count' => $foilCards,
            'total_count' => $totalCards,
            'set_completion' => $setCompletion
        ];
    }
    
    /**
     * Get detailed statistics for stats tab
     */
    private function getDetailedStats($userId, $currentGame, $catalogBackend): array
    {
        // Condition distribution
        $conditionQuery = UserCollection::where('user_id', $userId)
            ->selectRaw('`condition`, COUNT(*) as count, SUM(quantity) as total_quantity')
            ->groupBy('condition');
        if ($currentGame) {
            $conditionQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
        }
        // Filter by catalog backend
        if ($catalogBackend === 'tcgdex') {
            $conditionQuery->whereNotNull('tcgdex_card_id');
        } else {
            $conditionQuery->whereNotNull('product_id');
        }
        $conditionDistribution = $conditionQuery
            ->get();
        
        // Cards with photos
        $cardsWithPhotosQuery = UserCollection::where('user_id', $userId)
            ->whereHas('photos');
        
        // Filter by catalog backend
        if ($catalogBackend === 'tcgdex') {
            $cardsWithPhotosQuery->whereNotNull('tcgdex_card_id');
            if ($currentGame) {
                $cardsWithPhotosQuery->whereHas('tcgdexCard', function($q) use ($currentGame) {
                    $q->whereHas('set', fn($sq) => $sq->where('game_id', $currentGame->id));
                });
            }
        } elseif ($catalogBackend === 'cmapi') {
            $cardsWithPhotosQuery->whereNotNull('cmapi_card_id');
            if ($currentGame) {
                // CMAPI uses string game code (e.g. lorcana, onepiece, riftbound)
                $cardsWithPhotosQuery->whereHas('cmapiCard', function ($q) use ($currentGame) {
                    $q->where('game', $currentGame->slug);
                });
            }
        } else {
            // TCGCSV
            $cardsWithPhotosQuery->whereNotNull('product_id');
            if ($currentGame) {
                $cardsWithPhotosQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
            }
        }
        $cardsWithPhotos = $cardsWithPhotosQuery->count();
        
        // Duplicate cards (quantity > 1)
        $duplicateCardsQuery = UserCollection::where('user_id', $userId)
            ->where('quantity', '>', 1);
        
        // Filter by catalog backend
        if ($catalogBackend === 'tcgdex') {
            $duplicateCardsQuery->whereNotNull('tcgdex_card_id');
            if ($currentGame) {
                $duplicateCardsQuery->whereHas('tcgdexCard', function($q) use ($currentGame) {
                    $q->whereHas('set', fn($sq) => $sq->where('game_id', $currentGame->id));
                });
            }
        } elseif ($catalogBackend === 'cmapi') {
            $duplicateCardsQuery->whereNotNull('cmapi_card_id');
            if ($currentGame) {
                $duplicateCardsQuery->whereHas('cmapiCard', function ($q) use ($currentGame) {
                    $q->where('game', $currentGame->slug);
                });
            }
        } else {
            // TCGCSV
            $duplicateCardsQuery->whereNotNull('product_id');
            if ($currentGame) {
                $duplicateCardsQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
            }
        }
        $duplicateCards = $duplicateCardsQuery->count();
        
        // Set statistics
        if ($catalogBackend === 'tcgdex') {
            // TCGDEX
            $setStatsQuery = UserCollection::where('user_id', $userId)
                ->join('tcgdx_cards', 'user_collection.tcgdex_card_id', '=', 'tcgdx_cards.id')
                ->selectRaw('COUNT(DISTINCT tcgdx_cards.set_tcgdx_id) as total_sets')
                ->whereNotNull('user_collection.tcgdex_card_id')
                ->first();
            $setStats = $setStatsQuery;
        } else {
            // TCGCSV
            $setStatsQuery = UserCollection::where('user_id', $userId)
                ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.product_id')
                ->join('tcgcsv_groups', 'tcgcsv_products.group_id', '=', 'tcgcsv_groups.group_id')
                ->selectRaw('COUNT(DISTINCT tcgcsv_groups.group_id) as total_sets')
                ->whereNotNull('user_collection.product_id');
            if ($currentGame) {
                $setStatsQuery->where('tcgcsv_groups.game_id', $currentGame->id);
            }
            $setStats = $setStatsQuery->first();
        }
        
        // Top 5 sets by completion
        if ($catalogBackend === 'tcgdex') {
            // TCGDEX
            $topSetsQuery = UserCollection::where('user_id', $userId)
                ->join('tcgdx_cards', 'user_collection.tcgdex_card_id', '=', 'tcgdx_cards.id')
                ->join('tcgdx_sets', 'tcgdx_cards.set_tcgdx_id', '=', 'tcgdx_sets.id')
                ->selectRaw('tcgdx_sets.id as set_id, tcgdx_sets.name, COUNT(DISTINCT user_collection.tcgdex_card_id) as owned_count')
                ->whereNotNull('user_collection.tcgdex_card_id')
                ->groupBy('tcgdx_sets.id', 'tcgdx_sets.name')
                ->orderBy('owned_count', 'desc')
                ->limit(5)
                ->get()
                ->map(function($set) {
                    // Extract English name from JSON
                    $setName = $set->name;
                    if (is_string($setName) && str_starts_with($setName, '{')) {
                        $setName = json_decode($setName, true);
                    }
                    $setNameEn = is_array($setName) ? ($setName['en'] ?? $setName['fr'] ?? 'Unknown') : $setName;
                    
                    $totalInSet = \App\Models\Tcgdx\TcgdxCard::where('set_tcgdx_id', $set->set_id)->count();
                    $set->name = $setNameEn;
                    $set->total_in_set = $totalInSet;
                    $set->completion_percentage = $totalInSet > 0 ? round(($set->owned_count / $totalInSet) * 100, 1) : 0;
                    return $set;
                });
            $topSets = $topSetsQuery;
        } else {
            // TCGCSV
            $topSetsQuery = UserCollection::where('user_id', $userId)
                ->join('tcgcsv_products', 'user_collection.product_id', '=', 'tcgcsv_products.product_id')
                ->join('tcgcsv_groups', 'tcgcsv_products.group_id', '=', 'tcgcsv_groups.group_id')
                ->selectRaw('tcgcsv_groups.group_id, tcgcsv_groups.name, COUNT(DISTINCT user_collection.product_id) as owned_count')
                ->whereNotNull('user_collection.product_id')
                ->groupBy('tcgcsv_groups.group_id', 'tcgcsv_groups.name')
                ->orderBy('owned_count', 'desc')
                ->limit(5);
            if ($currentGame) {
                $topSetsQuery->where('tcgcsv_groups.game_id', $currentGame->id);
            }
            $topSets = $topSetsQuery->get()
                ->map(function($set) use ($currentGame) {
                    $totalQuery = TcgcsvProduct::where('group_id', $set->group_id);
                    if ($currentGame) {
                        $totalQuery->where('game_id', $currentGame->id);
                    }
                    $totalInSet = $totalQuery->count();
                    $set->total_in_set = $totalInSet;
                    $set->completion_percentage = $totalInSet > 0 ? round(($set->owned_count / $totalInSet) * 100, 1) : 0;
                    return $set;
                });
        }
        
        // Timeline - cards added by month (last 6 months)
        $timelineQuery = UserCollection::where('user_id', $userId)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc');
        if ($currentGame) {
            $timelineQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
        }
        // Filter by catalog backend
        if ($catalogBackend === 'tcgdex') {
            $timelineQuery->whereNotNull('tcgdex_card_id');
        } else {
            $timelineQuery->whereNotNull('product_id');
        }
        $timeline = $timelineQuery->get();
        
        // Foil cards count
        $foilCardsQuery = UserCollection::where('user_id', $userId)
            ->where('is_foil', true);
        
        // Filter by catalog backend
        if ($catalogBackend === 'tcgdex') {
            $foilCardsQuery->whereNotNull('tcgdex_card_id');
            if ($currentGame) {
                $foilCardsQuery->whereHas('tcgdexCard', function($q) use ($currentGame) {
                    $q->whereHas('set', fn($sq) => $sq->where('game_id', $currentGame->id));
                });
            }
        } elseif ($catalogBackend === 'cmapi') {
            $foilCardsQuery->whereNotNull('cmapi_card_id');
            if ($currentGame) {
                $foilCardsQuery->whereHas('cmapiCard', function ($q) use ($currentGame) {
                    $q->where('game', $currentGame->slug);
                });
            }
        } else {
            // TCGCSV
            $foilCardsQuery->whereNotNull('product_id');
            if ($currentGame) {
                $foilCardsQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
            }
        }
        $foilCards = $foilCardsQuery->sum('quantity');
        
        // Rare cards count (rarity contains 'rare', 'ultra', 'secret', 'holo')
        $rareCardsQuery = UserCollection::where('user_id', $userId);
        
        if ($catalogBackend === 'tcgdex') {
            $rareCardsQuery->whereNotNull('tcgdex_card_id')
                ->whereHas('tcgdexCard', function($q) use ($currentGame) {
                    $q->where(function($rq) {
                        $rq->where('rarity', 'like', '%rare%')
                          ->orWhere('rarity', 'like', '%ultra%')
                          ->orWhere('rarity', 'like', '%secret%')
                          ->orWhere('rarity', 'like', '%holo%');
                    });
                    if ($currentGame) {
                        $q->whereHas('set', fn($sq) => $sq->where('game_id', $currentGame->id));
                    }
                });
        } elseif ($catalogBackend === 'cmapi') {
            $rareCardsQuery->whereNotNull('cmapi_card_id')
                ->whereHas('cmapiCard', function($q) use ($currentGame) {
                    $q->where(function($rq) {
                        $rq->where('rarity', 'like', '%rare%')
                          ->orWhere('rarity', 'like', '%legendary%')
                          ->orWhere('rarity', 'like', '%enchanted%')
                          ->orWhere('rarity', 'like', '%super%');
                    });
                    if ($currentGame) {
                        // CMAPI uses string game code (e.g. lorcana, onepiece, riftbound)
                        $q->where('game', $currentGame->slug);
                    }
                });
        } else {
            // TCGCSV
            $rareCardsQuery->whereNotNull('product_id')
                ->whereHas('card', function($q) use ($currentGame) {
                    $q->where(function($rq) {
                        $rq->where('rarity', 'like', '%rare%')
                          ->orWhere('rarity', 'like', '%ultra%')
                          ->orWhere('rarity', 'like', '%secret%')
                          ->orWhere('rarity', 'like', '%holo%');
                    });
                    if ($currentGame) {
                        $q->where('game_id', $currentGame->id);
                    }
                });
        }
        $rareCards = $rareCardsQuery->sum('quantity');
        
        return [
            'condition_distribution' => $conditionDistribution,
            'cards_with_photos' => $cardsWithPhotos,
            'duplicate_cards' => $duplicateCards,
            'foil_cards' => $foilCards,
            'rare_cards' => $rareCards,
            'total_sets' => $setStats->total_sets ?? 0,
            'top_sets' => $topSets,
            'timeline' => $timeline
        ];
    }

    /**
     * Add a card to user's collection
     */
    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:tcgcsv_products,product_id',
            'quantity' => 'nullable|integer|min:1|max:99',
            'condition' => 'nullable|string|in:mint,near_mint,excellent,good,light_played,played,poor',
            'is_foil' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $quantityToAdd = $validated['quantity'] ?? 1;

        // Check card limit
        if (!\Gate::forUser($user)->allows('addCards', $quantityToAdd)) {
            $limit = $user->cardLimit();
            $currentUsage = $user->currentCardUsage();
            
            return back()->with('error', __('limits.cards.reached.title'))
                ->with('error_detail', __('limits.cards.reached.body_adding', [
                    'amount' => $quantityToAdd,
                    'limit' => $limit,
                    'used' => $currentUsage,
                ]));
        }

        // Check if card already exists with same condition/foil
        $existing = UserCollection::where('user_id', Auth::id())
            ->where('product_id', $validated['product_id'])
            ->where('condition', $validated['condition'] ?? null)
            ->where('is_foil', $validated['is_foil'] ?? false)
            ->first();

        if ($existing) {
            // Increment quantity
            $existing->increment('quantity', $quantityToAdd);
            $message = 'Card quantity updated in your collection!';
        } else {
            // Get card price from catalog
            $card = TcgcsvProduct::find($validated['product_id']);
            $price = null;
            $currency = 'USD';
            
            if ($card) {
                // Try to get price from latest price record
                $latestPrice = $card->prices()->orderBy('updated_at', 'desc')->first();
                if ($latestPrice && $latestPrice->market_price) {
                    $price = $latestPrice->market_price;
                    $currency = 'USD';
                }
            }
            
            // Create new entry
            UserCollection::create([
                'user_id' => Auth::id(),
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'] ?? 1,
                'condition' => $validated['condition'] ?? null,
                'is_foil' => $validated['is_foil'] ?? false,
                'notes' => $validated['notes'] ?? null,
                'cached_price' => $price,
                'cached_price_currency' => $currency,
                'cached_price_updated_at' => $price ? now() : null,
            ]);
            $message = 'Card added to your collection!';
        }

        return back()->with('success', $message);
    }

    /**
     * Add a TCGDEX card to user's collection
     */
    public function addTcgdex(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tcgdex_card_id' => 'required|integer|exists:tcgdx_cards,id',
            'quantity' => 'nullable|integer|min:1|max:99',
            'condition' => 'nullable|string|in:mint,near_mint,excellent,good,light_played,played,poor',
            'is_foil' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $quantityToAdd = $validated['quantity'] ?? 1;

        // Check card limit
        if (!\Gate::forUser($user)->allows('addCards', $quantityToAdd)) {
            $limit = $user->cardLimit();
            $currentUsage = $user->currentCardUsage();
            
            return back()->with('error', __('limits.cards.reached.title'))
                ->with('error_detail', __('limits.cards.reached.body_adding', [
                    'amount' => $quantityToAdd,
                    'limit' => $limit,
                    'used' => $currentUsage,
                ]));
        }

        // Check if card already exists with same condition/foil
        $existing = UserCollection::where('user_id', Auth::id())
            ->where('tcgdex_card_id', $validated['tcgdex_card_id'])
            ->where('condition', $validated['condition'] ?? null)
            ->where('is_foil', $validated['is_foil'] ?? false)
            ->first();

        if ($existing) {
            // Increment quantity
            $existing->increment('quantity', $quantityToAdd);
            $message = 'Card quantity updated in your collection!';
        } else {
            // Get card price from catalog
            $card = \App\Models\Tcgdx\TcgdxCard::find($validated['tcgdex_card_id']);
            $price = null;
            $currency = 'EUR';
            
            if ($card) {
                // Use EUR price if available, fallback to USD
                if ($card->price_eur && $card->price_eur > 0) {
                    $price = $card->price_eur;
                    $currency = 'EUR';
                } elseif ($card->price_usd && $card->price_usd > 0) {
                    $price = $card->price_usd;
                    $currency = 'USD';
                }
            }
            
            // Create new entry
            UserCollection::create([
                'user_id' => Auth::id(),
                'tcgdex_card_id' => $validated['tcgdex_card_id'],
                'quantity' => $validated['quantity'] ?? 1,
                'condition' => $validated['condition'] ?? null,
                'is_foil' => $validated['is_foil'] ?? false,
                'notes' => $validated['notes'] ?? null,
                'cached_price' => $price,
                'cached_price_currency' => $currency,
                'cached_price_updated_at' => $price ? now() : null,
            ]);
            $message = 'Card added to your collection!';
        }

        return back()->with('success', $message);
    }

    /**
     * Add a CMAPI card (Lorcana/One Piece) to user's collection
     */
    public function addCmapi(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cmapi_card_id' => 'required|string|max:100|exists:cmapi_cards,cmapi_id',
            'quantity' => 'nullable|integer|min:1|max:99',
            'condition' => 'nullable|string|in:mint,near_mint,excellent,good,light_played,played,poor',
            'is_foil' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $quantityToAdd = $validated['quantity'] ?? 1;

        // Check card limit
        if (!\Gate::forUser($user)->allows('addCards', $quantityToAdd)) {
            $limit = $user->cardLimit();
            $currentUsage = $user->currentCardUsage();
            
            return back()->with('error', __('limits.cards.reached.title'))
                ->with('error_detail', __('limits.cards.reached.body_adding', [
                    'amount' => $quantityToAdd,
                    'limit' => $limit,
                    'used' => $currentUsage,
                ]));
        }

        // Check if card already exists with same condition/foil
        $existing = UserCollection::where('user_id', Auth::id())
            ->where('cmapi_card_id', $validated['cmapi_card_id'])
            ->where('condition', $validated['condition'] ?? null)
            ->where('is_foil', $validated['is_foil'] ?? false)
            ->first();

        if ($existing) {
            // Increment quantity
            $existing->increment('quantity', $quantityToAdd);
            $message = 'Card quantity updated in your collection!';
        } else {
            // Get card price from catalog
            $card = \App\Models\Cmapi\CmapiCard::where('cmapi_id', $validated['cmapi_card_id'])->first();
            $price = null;
            $currency = 'EUR';
            
            if ($card && $card->price_eur && $card->price_eur > 0) {
                $price = $card->price_eur;
                $currency = 'EUR';
            }
            
            // Create new entry
            UserCollection::create([
                'user_id' => Auth::id(),
                'cmapi_card_id' => $validated['cmapi_card_id'],
                'quantity' => $validated['quantity'] ?? 1,
                'condition' => $validated['condition'] ?? null,
                'is_foil' => $validated['is_foil'] ?? false,
                'notes' => $validated['notes'] ?? null,
                'cached_price' => $price,
                'cached_price_currency' => $currency,
                'cached_price_updated_at' => $price ? now() : null,
            ]);
            $message = 'Card added to your collection!';
        }

        return back()->with('success', $message);
    }

    /**
     * Remove a card from collection
     */
    public function remove($id): RedirectResponse
    {
        $collectionItem = UserCollection::findOrFail($id);
        
        // Authorization check
        if ($collectionItem->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $collectionItem->delete();

        return back()->with('success', 'Card removed from collection!');
    }

    /**
     * Update card quantity or details
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $collectionItem = UserCollection::findOrFail($id);
        
        // Authorization check
        if ($collectionItem->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
            'condition' => 'nullable|string|in:mint,near_mint,excellent,good,light_played,played,poor',
            'is_foil' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $collectionItem->update($validated);

        return back()->with('success', 'Collection item updated!');
    }

    /**
     * Check if a card is in user's collection
     */
    public function checkCard(int $productId)
    {
        $items = UserCollection::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->get();

        return response()->json([
            'in_collection' => $items->isNotEmpty(),
            'total_quantity' => $items->sum('quantity'),
            'items' => $items,
        ]);
    }
    
    /**
     * Calculate total collection value in USD and EUR
     * Uses cached prices for performance, falls back to real-time queries if cache is null
     */
    private function calculateCollectionValue($userId, $currentGame, $catalogBackend, $rarityFilter = null): array
    {
        $user = \App\Models\User::find($userId);
        $preferredCurrency = $user->preferred_currency ?? 'USD';
        
        // Try cached prices first (fast query)
        $cachedQuery = UserCollection::where('user_id', $userId)
            ->whereNotNull('cached_price');
            
        if ($catalogBackend === 'tcgdex') {
            $cachedQuery->whereNotNull('tcgdex_card_id');
            // No currentGame filter for TCGDEX (it's always Pokemon)
            
            // Apply rarity filter for TCGDEX
            if ($rarityFilter) {
                $cachedQuery->whereHas('tcgdexCard', function($q) use ($rarityFilter) {
                    $q->where('rarity', $rarityFilter);
                });
            }
        } else {
            $cachedQuery->whereNotNull('product_id');
            // Filter by current game (only for TCGCSV)
            if ($currentGame) {
                $cachedQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
            }
            
            // Apply rarity filter for TCGCSV
            if ($rarityFilter) {
                $cachedQuery->whereHas('card', function($q) use ($rarityFilter) {
                    $q->where('rarity', $rarityFilter);
                });
            }
        }
        
        $cachedItems = $cachedQuery->get();
        
        // Calculate from cached prices
        $totalValueUsd = 0;
        $totalValueEur = 0;
        $cardsWithCachedPrices = $cachedItems->count();
        
        foreach ($cachedItems as $item) {
            // All cached prices are now in EUR (from price_eur or cardmarket_price_eur)
            $totalValueEur += $item->cached_price * $item->quantity;
            // Convert to USD (approximate)
            $totalValueUsd += ($item->cached_price * 1.10) * $item->quantity;
        }
        
        // Fallback: Get items without cached prices and calculate real-time
        $uncachedQuery = UserCollection::where('user_id', $userId)
            ->whereNull('cached_price');
            
        if ($catalogBackend === 'tcgdex') {
            $uncachedQuery->whereNotNull('tcgdex_card_id')
                         ->with('tcgdexCard');
            // No currentGame filter for TCGDEX
            
            // Apply rarity filter for TCGDEX
            if ($rarityFilter) {
                $uncachedQuery->whereHas('tcgdexCard', function($q) use ($rarityFilter) {
                    $q->where('rarity', $rarityFilter);
                });
            }
        } else {
            $uncachedQuery->whereNotNull('product_id')
                         ->with([
                             'card.prices' => function($q) {
                                 $q->latest('snapshot_at')->limit(1);
                             },
                             'card.rapidapiCard',
                             'card.cardmarketProduct.latestPriceQuote'
                         ]);
            // Filter by current game (only for TCGCSV)
            if ($currentGame) {
                $uncachedQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
            }
            
            // Apply rarity filter for TCGCSV
            if ($rarityFilter) {
                $uncachedQuery->whereHas('card', function($q) use ($rarityFilter) {
                    $q->where('rarity', $rarityFilter);
                });
            }
        }
        
        $uncachedItems = $uncachedQuery->get();
        
        foreach ($uncachedItems as $item) {
            // TCGDEX pricing
            if ($item->tcgdex_card_id && $item->tcgdexCard) {
                $pricing = $item->tcgdexCard->raw['pricing'] ?? null;
                if ($pricing && isset($pricing['cardmarket']['averageSellPrice'])) {
                    $priceEur = $pricing['cardmarket']['averageSellPrice'];
                    $totalValueEur += $priceEur * $item->quantity;
                    $totalValueUsd += ($priceEur * 1.10) * $item->quantity;
                }
                continue;
            }
            
            // TCGCSV pricing
            if ($item->product_id && $item->card) {
                // USD price from TCGPlayer
                $latestPrice = $item->card->prices->first();
                $marketPriceUsd = $latestPrice?->market_price ?? 0;
                
                if ($marketPriceUsd > 0) {
                    $totalValueUsd += $marketPriceUsd * $item->quantity;
                }
                
                // EUR price - Priority system
                $marketPriceEur = 0;
                
                // Priority 1: Cardmarket price quotes
                $cardmarketProduct = $item->card->cardmarketProduct;
                if ($cardmarketProduct) {
                    $latestQuote = $cardmarketProduct->latestPriceQuote;
                    if ($latestQuote && $latestQuote->trend > 0) {
                        $marketPriceEur = $latestQuote->trend;
                    } elseif ($latestQuote && $latestQuote->avg > 0) {
                        $marketPriceEur = $latestQuote->avg;
                    }
                }
                
                // Priority 2: Cardmarket EUR from tcgcsv_products
                if ($marketPriceEur === 0 && $item->card->cardmarket_price_eur && $item->card->cardmarket_price_eur > 0) {
                    $marketPriceEur = $item->card->cardmarket_price_eur;
                }
                
                // Priority 3: RapidAPI Cardmarket data
                if ($marketPriceEur === 0) {
                    $rapidapiCard = $item->card->rapidapiCard;
                    if ($rapidapiCard && isset($rapidapiCard->raw_data['prices']['cardmarket']['lowest_near_mint'])) {
                        $marketPriceEur = (float) $rapidapiCard->raw_data['prices']['cardmarket']['lowest_near_mint'];
                    }
                }
                
                if ($marketPriceEur > 0) {
                    $totalValueEur += $marketPriceEur * $item->quantity;
                }
            }
        }
        
        return [
            'total_value_usd' => round($totalValueUsd, 2),
            'total_value_eur' => round($totalValueEur, 2),
            'cards_with_prices_usd' => $cardsWithCachedPrices + $uncachedItems->count(),
            'cards_with_prices_eur' => $cardsWithCachedPrices + $uncachedItems->count(),
            'cached_items' => $cardsWithCachedPrices,
            'uncached_items' => $uncachedItems->count(),
        ];
    }

    /**
     * Upload a photo for a collection item (Premium only)
     */
    public function uploadPhoto(Request $request, UserCollection $collection)
    {
        // Authorization: must own the collection item
        if ($collection->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Authorization: must be premium
        if (!Gate::allows('uploadCardPhotos')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('photos.upload.not_allowed.title')
                ], 403);
            }
            return back()->with('error', __('photos.upload.not_allowed.title'));
        }

        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120', // 5MB max
        ]);

        $file = $request->file('photo');
        
        // Store in local storage (storage/app/private)
        $path = $file->store('user-card-photos/' . Auth::id(), 'local');
        
        // Create photo record
        $photo = \App\Models\UserCardPhoto::create([
            'user_id' => Auth::id(),
            'user_collection_id' => $collection->id,
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('photos.upload.success'),
                'photo' => $photo
            ]);
        }

        return back()->with('success', __('photos.upload.success'));
    }

    /**
     * Serve a photo file (owner only)
     */
    public function servePhoto(\App\Models\UserCardPhoto $photo)
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
    public function deletePhoto(\App\Models\UserCardPhoto $photo)
    {
        // Authorization: must own the photo
        if ($photo->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $photo->delete(); // Will also delete file via model event

        return back()->with('success', __('photos.delete.success'));
    }

    /**
     * Quick add card to collection (AJAX endpoint)
     */
    public function quickAdd(Request $request)
    {
        // Determine if this is TCGDEX, CMAPI, or TCGCSV card
        $isTcgdex = $request->has('tcgdex_card_id');
        $isCmapi = $request->has('cmapi_card_id');
        
        if ($isTcgdex) {
            $validated = $request->validate([
                'tcgdex_card_id' => 'required|integer|exists:tcgdx_cards,id',
                'quantity' => 'required|integer|min:1|max:100',
                'condition' => 'required|string|in:M,NM,LP,MP,HP,D',
            ]);
        } elseif ($isCmapi) {
            $validated = $request->validate([
                'cmapi_card_id' => 'required|string|max:100|exists:cmapi_cards,cmapi_id',
                'quantity' => 'required|integer|min:1|max:100',
                'condition' => 'required|string|in:M,NM,LP,MP,HP,D',
            ]);
        } else {
            $validated = $request->validate([
                'card_id' => 'required|integer|exists:tcgcsv_products,product_id',
                'quantity' => 'required|integer|min:1|max:100',
                'condition' => 'required|string|in:M,NM,LP,MP,HP,D',
            ]);
        }

        try {
            // Check if user already has this card
            $query = UserCollection::where('user_id', Auth::id())
                ->where('condition', $validated['condition']);
            
            if ($isTcgdex) {
                $query->where('tcgdex_card_id', $validated['tcgdex_card_id']);
            } elseif ($isCmapi) {
                $query->where('cmapi_card_id', $validated['cmapi_card_id']);
            } else {
                $query->where('product_id', $validated['card_id']);
            }
            
            $existingCard = $query->first();

            if ($existingCard) {
                // Update quantity if card already exists
                $existingCard->quantity += $validated['quantity'];
                $existingCard->save();
                
                return response()->json([
                    'success' => true,
                    'message' => __('dashboard.card_added_successfully'),
                    'action' => 'updated',
                    'new_quantity' => $existingCard->quantity,
                ]);
            } else {
                // Create new collection entry
                $price = null;
                $currency = 'USD';
                
                // Fetch price based on backend
                if ($isTcgdex) {
                    $card = \App\Models\Tcgdx\TcgdxCard::where('id', $validated['tcgdex_card_id'])->first();
                    if ($card) {
                        if ($card->price_eur && $card->price_eur > 0) {
                            $price = $card->price_eur;
                            $currency = 'EUR';
                        } elseif ($card->price_usd && $card->price_usd > 0) {
                            $price = $card->price_usd;
                            $currency = 'USD';
                        }
                    }
                } elseif ($isCmapi) {
                    $card = \App\Models\Cmapi\CmapiCard::where('cmapi_id', $validated['cmapi_card_id'])->first();
                    if ($card && $card->price_eur && $card->price_eur > 0) {
                        $price = $card->price_eur;
                        $currency = 'EUR';
                    }
                } else {
                    $card = \App\Models\TcgcsvProduct::where('product_id', $validated['card_id'])->first();
                    if ($card) {
                        $latestPrice = $card->prices()->orderBy('updated_at', 'desc')->first();
                        if ($latestPrice && $latestPrice->market_price) {
                            $price = $latestPrice->market_price;
                            $currency = 'USD';
                        }
                    }
                }
                
                $data = [
                    'user_id' => Auth::id(),
                    'quantity' => $validated['quantity'],
                    'condition' => $validated['condition'],
                    'is_foil' => false, // Default to non-foil for quick add
                    'cached_price' => $price,
                    'cached_price_currency' => $currency,
                    'cached_price_updated_at' => $price ? now() : null,
                ];
                
                if ($isTcgdex) {
                    $data['tcgdex_card_id'] = $validated['tcgdex_card_id'];
                } elseif ($isCmapi) {
                    $data['cmapi_card_id'] = $validated['cmapi_card_id'];
                } else {
                    $data['product_id'] = $validated['card_id'];
                }
                
                UserCollection::create($data);

                return response()->json([
                    'success' => true,
                    'message' => __('dashboard.card_added_successfully'),
                    'action' => 'created',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Quick add card error', [
                'user_id' => Auth::id(),
                'card_id' => $validated['card_id'] ?? null,
                'tcgdex_card_id' => $validated['tcgdex_card_id'] ?? null,
                'cmapi_card_id' => $validated['cmapi_card_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('dashboard.error_adding_card'),
            ], 500);
        }
    }

    /**
     * Add filtered collection cards to deck
     */
    public function addFilteredToDeck(Request $request)
    {
        $validated = $request->validate([
            'deck_id' => 'required|integer|exists:decks,id',
            'filters' => 'array',
        ]);

        $deck = \App\Models\Deck::findOrFail($validated['deck_id']);
        
        // Authorization check
        if ($deck->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this deck.',
            ], 403);
        }

        $filters = $validated['filters'] ?? [];
        $catalogBackend = catalog_backend();
        $currentGame = $request->attributes->get('currentGame');
        
        Log::info('Add filtered to deck', [
            'deck_id' => $deck->id,
            'filters' => $filters,
            'catalogBackend' => $catalogBackend,
            'currentGame' => $currentGame?->id,
        ]);
        
        // Get filtered collection (same logic as index method)
        $query = UserCollection::where('user_id', Auth::id());
        
        // Apply backend filter
        if ($catalogBackend === 'tcgdex') {
            $query->whereNotNull('tcgdex_card_id')
                ->with('tcgdexCard');
        } elseif ($catalogBackend === 'cmapi') {
            $query->whereNotNull('cmapi_card_id')
                ->with('cmapiCard');
        } else {
            $query->whereNotNull('product_id')
                ->with(['card.group', 'card.rapidapiCard']);
        }
        
        // Apply user filters
        if (!empty($filters['letter'])) {
            if ($catalogBackend === 'tcgdex') {
                $query->whereHas('tcgdexCard', function($q) use ($filters) {
                    $q->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.en")) LIKE ?', [$filters['letter'] . '%']);
                });
            } elseif ($catalogBackend === 'cmapi') {
                $query->whereHas('cmapiCard', function($q) use ($filters) {
                    $q->where('name', 'LIKE', $filters['letter'] . '%');
                });
            } else {
                $query->whereHas('card', function($q) use ($filters) {
                    $q->where('name', 'LIKE', $filters['letter'] . '%');
                });
            }
        }
        
        if (!empty($filters['set'])) {
            if ($catalogBackend === 'tcgdex') {
                $query->whereHas('tcgdexCard', function($q) use ($filters) {
                    $q->where('tcgdx_set_id', $filters['set']);
                });
            } elseif ($catalogBackend === 'cmapi') {
                $query->whereHas('cmapiCard', function($q) use ($filters) {
                    $q->where('set_name', $filters['set']);
                });
            } else {
                $query->whereHas('card.group', function($q) use ($filters) {
                    $q->where('name', $filters['set']);
                });
            }
        }
        
        if (!empty($filters['rarity'])) {
            if ($catalogBackend === 'tcgdex') {
                $query->whereHas('tcgdexCard', function($q) use ($filters) {
                    $q->where('rarity', $filters['rarity']);
                });
            } elseif ($catalogBackend === 'cmapi') {
                $query->whereHas('cmapiCard', function($q) use ($filters) {
                    $q->where('rarity', $filters['rarity']);
                });
            } else {
                $query->whereHas('card', function($q) use ($filters) {
                    $q->where('rarity', $filters['rarity']);
                });
            }
        }
        
        // Apply price range filter (Premium only)
        $minPrice = isset($filters['min_price']) ? (float)$filters['min_price'] : null;
        $maxPrice = isset($filters['max_price']) ? (float)$filters['max_price'] : null;
        
        if (($minPrice !== null || $maxPrice !== null)) {
            $canSeePrices = Gate::allows('seePrices');
            
            Log::info('Price filter check', [
                'minPrice' => $minPrice,
                'maxPrice' => $maxPrice,
                'canSeePrices' => $canSeePrices,
                'userTier' => Auth::user()->subscription_tier,
            ]);
            
            if ($canSeePrices) {
                $user = Auth::user();
                $preferredCurrency = $user->preferred_currency ?? 'EUR';
                
                // Get all collection IDs that match the price range
                $priceFilterQuery = UserCollection::where('user_id', Auth::id())
                    ->whereNotNull('cached_price')
                    ->where('cached_price', '>', 0)
                    ->select('id', 'cached_price', 'cached_price_currency');
                
                // Apply backend filter to price query
                if ($catalogBackend === 'tcgdex') {
                    $priceFilterQuery->whereNotNull('tcgdex_card_id');
                } elseif ($catalogBackend === 'cmapi') {
                    $priceFilterQuery->whereNotNull('cmapi_card_id');
                } else {
                    $priceFilterQuery->whereNotNull('product_id');
                    if ($currentGame) {
                        $priceFilterQuery->whereHas('card', fn($q) => $q->where('game_id', $currentGame->id));
                    }
                }
                
                $priceItems = $priceFilterQuery->get();
                
                Log::info('Price items before filtering', [
                    'total_items_with_price' => $priceItems->count(),
                    'preferredCurrency' => $preferredCurrency,
                ]);
                
                $validIds = $priceItems->filter(function($item) use ($minPrice, $maxPrice, $preferredCurrency) {
                    $currency = $item->cached_price_currency ?? 'EUR';
                    $priceInPreferred = \App\Services\CurrencyService::convert($item->cached_price, $currency, $preferredCurrency);
                    
                    $matchesMin = $minPrice === null || $priceInPreferred >= $minPrice;
                    $matchesMax = $maxPrice === null || $priceInPreferred <= $maxPrice;
                    
                    return $matchesMin && $matchesMax;
                })->pluck('id')->toArray();
                
                Log::info('Price filter result', [
                    'valid_ids_count' => count($validIds),
                ]);
                
                if (!empty($validIds)) {
                    $query->whereIn('user_collection.id', $validIds);
                } else {
                    // No items match the price filter, return empty result
                    $query->whereRaw('1 = 0');
                }
            } else {
                Log::warning('User tried to filter by price without premium access');
            }
        }
        
        $cards = $query->get();
        $cardsAdded = 0;
        
        Log::info('Found cards to add', [
            'count' => $cards->count(),
        ]);
        
        foreach ($cards as $collectionItem) {
            // Add to deck based on backend
            if ($catalogBackend === 'tcgdex' && $collectionItem->tcgdex_card_id) {
                $existing = \App\Models\DeckCard::where('deck_id', $deck->id)
                    ->where('tcgdex_card_id', $collectionItem->tcgdex_card_id)
                    ->first();
                    
                if (!$existing) {
                    \App\Models\DeckCard::create([
                        'deck_id' => $deck->id,
                        'tcgdex_card_id' => $collectionItem->tcgdex_card_id,
                        'quantity' => 1,
                    ]);
                    $cardsAdded++;
                }
            } elseif ($catalogBackend === 'cmapi' && $collectionItem->cmapi_card_id) {
                $existing = \App\Models\DeckCard::where('deck_id', $deck->id)
                    ->where('cmapi_card_id', $collectionItem->cmapi_card_id)
                    ->first();
                    
                if (!$existing) {
                    $card = $collectionItem->cmapiCard;
                    \App\Models\DeckCard::create([
                        'deck_id' => $deck->id,
                        'cmapi_card_id' => $collectionItem->cmapi_card_id,
                        'quantity' => 1,
                        'cached_price' => $card->price_eur ?? null,
                        'cached_price_currency' => 'EUR',
                        'cached_price_updated_at' => $card->price_eur ? now() : null,
                    ]);
                    $cardsAdded++;
                }
            } else if ($collectionItem->product_id) {
                $existing = \App\Models\DeckCard::where('deck_id', $deck->id)
                    ->where('product_id', $collectionItem->product_id)
                    ->first();
                    
                if (!$existing) {
                    $card = $collectionItem->card;
                    $price = $card->prices->first()->price ?? null;
                    
                    \App\Models\DeckCard::create([
                        'deck_id' => $deck->id,
                        'product_id' => $collectionItem->product_id,
                        'quantity' => 1,
                        'cached_price' => $price,
                        'cached_price_currency' => 'USD',
                        'cached_price_updated_at' => $price ? now() : null,
                    ]);
                    $cardsAdded++;
                }
            }
        }
        
        Log::info('Cards added to deck', [
            'deck_id' => $deck->id,
            'cards_added' => $cardsAdded,
        ]);
        
        return response()->json([
            'success' => true,
            'cards_added' => $cardsAdded,
            'deck_id' => $deck->id,
        ]);
    }

    /**
     * Create deck and add filtered collection cards
     */
    public function createDeckWithFiltered(Request $request)
    {
        $validated = $request->validate([
            'deck_name' => 'required|string|max:255',
            'filters' => 'array',
        ]);
        
        // Check if user can create another deck
        if (!Auth::user()->canCreateAnotherDeck()) {
            return response()->json([
                'success' => false,
                'message' => __('decks/index.limit_reached'),
            ], 403);
        }
        
        $currentGame = $request->attributes->get('currentGame');
        
        // Create deck
        $deck = \App\Models\Deck::create([
            'user_id' => Auth::id(),
            'game_id' => $currentGame ? $currentGame->id : 1,
            'name' => $validated['deck_name'],
        ]);
        
        // Add filtered cards to deck
        $addRequest = new Request([
            'deck_id' => $deck->id,
            'filters' => $validated['filters'],
        ]);
        $addRequest->attributes->add(['currentGame' => $currentGame]);
        
        $result = $this->addFilteredToDeck($addRequest);
        $resultData = $result->getData(true);
        
        return response()->json([
            'success' => true,
            'deck_id' => $deck->id,
            'cards_added' => $resultData['cards_added'],
        ]);
    }

    /**
     * Add selected collection cards to deck
     */
    public function addSelectedToDeck(Request $request)
    {
        $validated = $request->validate([
            'deck_id' => 'required|integer|exists:decks,id',
            'cards' => 'required|array|min:1',
            'cards.*.collectionId' => 'required',
            'cards.*.cardId' => 'required',
            'cards.*.backend' => 'required|string',
        ]);

        $deck = \App\Models\Deck::findOrFail($validated['deck_id']);
        
        if ($deck->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this deck.',
            ], 403);
        }

        $cardsAdded = 0;
        
        foreach ($validated['cards'] as $cardData) {
            $backend = $cardData['backend'];
            $cardId = $cardData['cardId'];
            
            if ($backend === 'tcgdex') {
                $existing = \App\Models\DeckCard::where('deck_id', $deck->id)
                    ->where('tcgdex_card_id', $cardId)
                    ->first();
                    
                if ($existing) {
                    $existing->quantity += 1;
                    $existing->save();
                } else {
                    $card = \App\Models\Tcgdx\TcgdxCard::where('tcgdex_id', $cardId)->first();
                    $price = null;
                    $currency = 'USD';
                    
                    if ($card) {
                        if ($card->price_eur && $card->price_eur > 0) {
                            $price = $card->price_eur;
                            $currency = 'EUR';
                        } elseif ($card->price_usd && $card->price_usd > 0) {
                            $price = $card->price_usd;
                            $currency = 'USD';
                        }
                    }
                    
                    \App\Models\DeckCard::create([
                        'deck_id' => $deck->id,
                        'tcgdex_card_id' => $cardId,
                        'quantity' => 1,
                        'cached_price' => $price,
                        'cached_price_currency' => $currency,
                        'cached_price_updated_at' => $price ? now() : null,
                    ]);
                }
                $cardsAdded++;
            } elseif ($backend === 'cmapi') {
                $existing = \App\Models\DeckCard::where('deck_id', $deck->id)
                    ->where('cmapi_card_id', $cardId)
                    ->first();
                    
                if ($existing) {
                    $existing->quantity += 1;
                    $existing->save();
                } else {
                    $card = \App\Models\Cmapi\CmapiCard::where('cmapi_id', $cardId)->first();
                    \App\Models\DeckCard::create([
                        'deck_id' => $deck->id,
                        'cmapi_card_id' => $cardId,
                        'quantity' => 1,
                        'cached_price' => $card->price_eur ?? null,
                        'cached_price_currency' => 'EUR',
                        'cached_price_updated_at' => $card->price_eur ? now() : null,
                    ]);
                }
                $cardsAdded++;
            } else {
                $existing = \App\Models\DeckCard::where('deck_id', $deck->id)
                    ->where('product_id', $cardId)
                    ->first();
                    
                if ($existing) {
                    $existing->quantity += 1;
                    $existing->save();
                } else {
                    $card = \App\Models\TcgcsvProduct::where('product_id', $cardId)->first();
                    $price = null;
                    if ($card && $card->prices && $card->prices->first()) {
                        $price = $card->prices->first()->price;
                    }
                    
                    \App\Models\DeckCard::create([
                        'deck_id' => $deck->id,
                        'product_id' => $cardId,
                        'quantity' => 1,
                        'cached_price' => $price,
                        'cached_price_currency' => 'USD',
                        'cached_price_updated_at' => $price ? now() : null,
                    ]);
                }
                $cardsAdded++;
            }
        }
        
        return response()->json([
            'success' => true,
            'cards_added' => $cardsAdded,
            'deck_id' => $deck->id,
        ]);
    }

    /**
     * Update quantity of a collection item
     */
    public function updateQuantity(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);
        
        $item = UserCollection::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        $item->quantity = $validated['quantity'];
        $item->save();
        
        return response()->json([
            'success' => true,
            'quantity' => $item->quantity,
            'message' => 'Quantity updated successfully',
        ]);
    }
    
    /**
     * Create deck and add selected collection cards
     */
    public function createDeckWithSelected(Request $request)
    {
        try {
            \Log::info('Creating deck with selected cards', ['request' => $request->all()]);
            
            $validated = $request->validate([
                'deck_name' => 'required|string|max:255',
                'cards' => 'required|array|min:1',
                'cards.*.collectionId' => 'required',
                'cards.*.cardId' => 'required',
                'cards.*.backend' => 'required|string',
            ]);
            
            if (!Auth::user()->canCreateAnotherDeck()) {
                return response()->json([
                    'success' => false,
                    'message' => __('decks/index.limit_reached'),
                ], 403);
            }
            
            $currentGame = $request->attributes->get('currentGame');
            
            $deck = \App\Models\Deck::create([
                'user_id' => Auth::id(),
                'game_id' => $currentGame ? $currentGame->id : 1,
                'name' => $validated['deck_name'],
            ]);
            
            \Log::info('Deck created', ['deck_id' => $deck->id]);
            
            $addRequest = new Request([
                'deck_id' => $deck->id,
                'cards' => $validated['cards'],
            ]);
            $addRequest->attributes->add(['currentGame' => $currentGame]);
            
            $result = $this->addSelectedToDeck($addRequest);
            $resultData = $result->getData(true);
            
            \Log::info('Cards added to deck', ['result' => $resultData]);
            
            return response()->json([
                'success' => true,
                'deck_id' => $deck->id,
                'cards_added' => $resultData['cards_added'],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating deck with selected cards', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
